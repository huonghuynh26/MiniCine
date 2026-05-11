<?php
require_once __DIR__ . '/../config/db.php';

function getSeatPrice(string $type): int {
    $db = db();
    $stmt = $db->prepare("SELECT price FROM tblPrices WHERE seat_type=?");
    $stmt->bind_param('s', $type);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['price'] ?? 0);
}

function getFlashSale(int $showId): ?array {
    $db = db();
    $stmt = $db->prepare("
        SELECT fs.*, s.start_time, s.end_time
        FROM tblFlashSales fs
        JOIN tblShows s ON s.id = fs.show_id
        WHERE fs.show_id = ? AND fs.is_active = 1
        ORDER BY fs.discount_pct DESC
    ");
    $stmt->bind_param('i', $showId);
    $stmt->execute();
    $sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (empty($sales)) return null;

    $now       = time();
    $startTime = strtotime($sales[0]['start_time']);
    $endTime   = strtotime($sales[0]['end_time']);
    $pre2h     = $startTime - 2 * 3600;
    $post15m   = $startTime + 15 * 60;
    $best      = null;

    foreach ($sales as $sale) {
        $disc = (int)$sale['discount_pct'];
        if ($sale['trigger_type'] === 'manual' && $now <= $endTime) {
            if (!$best || $disc > $best['discount_pct'])
                $best = ['discount_pct' => $disc, 'reason' => "Flash Sale -{$disc}%"];
        }
        if ($sale['trigger_type'] === 'post15m' && $now >= $post15m && $now <= $endTime) {
            if (!$best || $disc > $best['discount_pct'])
                $best = ['discount_pct' => $disc, 'reason' => "Flash Sale -{$disc}% (sau giờ chiếu)"];
        }
        if ($sale['trigger_type'] === 'pre2h' && $now >= $pre2h && $now < $startTime) {
            $pct = getAvailableSeatPct($showId);
            if ($pct >= 30 && (!$best || $disc > $best['discount_pct']))
                $best = ['discount_pct' => $disc, 'reason' => "Flash Sale -{$disc}% (trước giờ chiếu)"];
        }
    }
    return $best;
}

function getAvailableSeatPct(int $showId): int {
    $db = db();
    $stmt = $db->prepare("
        SELECT COUNT(*) as total,
          SUM(CASE WHEN ss.status='available' OR ss.status IS NULL THEN 1 ELSE 0 END) as avail
        FROM tblSeats s
        JOIN tblShows sh ON sh.room_id = s.room_id AND sh.id = ?
        LEFT JOIN tblSeatStatus ss ON ss.seat_id=s.id AND ss.show_id=?
    ");
    $stmt->bind_param('ii', $showId, $showId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row['total']) return 0;
    return (int)round($row['avail'] / $row['total'] * 100);
}

// ─── Get seats layout ─────────────────────────────────────────────────────────
// $releaseMyHold = true khi user load trang booking lần đầu → nhả ghế cũ
function getSeatsLayout(int $showId, bool $releaseMyHold = false): array {
    $db = db();

    // Release expired holds
    $db->query("
        UPDATE tblSeatStatus
        SET status='available', held_until=NULL, held_by=NULL,
            version_number = version_number + 1
        WHERE status='held' AND held_until < NOW()
    ");

    // Nếu user mở lại trang → release ghế đang held của chính họ trong show này
    if ($releaseMyHold) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($userId) {
            $stmt = $db->prepare("
                UPDATE tblSeatStatus
                SET status='available', held_by=NULL, held_until=NULL,
                    version_number = version_number + 1
                WHERE show_id=? AND held_by=? AND status='held'
            ");
            $stmt->bind_param('ii', $showId, $userId);
            $stmt->execute();
        }
    }

    $stmt = $db->prepare("
        SELECT s.id, s.row, s.number, s.type,
               COALESCE(ss.status,'available') as status,
               ss.version_number,
               ss.held_by
        FROM tblSeats s
        JOIN tblShows sh ON sh.room_id = s.room_id AND sh.id = ?
        LEFT JOIN tblSeatStatus ss ON ss.seat_id = s.id AND ss.show_id = ?
        ORDER BY s.row, s.number
    ");
    $stmt->bind_param('ii', $showId, $showId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ─── Hold seats ───────────────────────────────────────────────────────────────
function holdSeats(int $showId, array $seatIds, int $userId): array {
    $db = db();
    $db->begin_transaction();
    try {
        $held = []; $failed = [];
        $holdMinutes = SEAT_HOLD_MINUTES;

        foreach ($seatIds as $seatId) {
            $seatId = (int)$seatId;
            $stmt = $db->prepare("
                SELECT id, status, version_number FROM tblSeatStatus
                WHERE show_id=? AND seat_id=? FOR UPDATE
            ");
            $stmt->bind_param('ii', $showId, $seatId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if (!$row) {
                $ins = $db->prepare("
                    INSERT INTO tblSeatStatus (show_id,seat_id,status,version_number,held_until,held_by)
                    VALUES (?,?,'held',1,DATE_ADD(NOW(), INTERVAL {$holdMinutes} MINUTE),?)
                ");
                $ins->bind_param('iii', $showId, $seatId, $userId);
                $ins->execute();
                $held[] = $seatId;
            } elseif ($row['status'] === 'available') {
                $newVer = $row['version_number'] + 1;
                $upd = $db->prepare("
                    UPDATE tblSeatStatus
                    SET status='held', version_number=?,
                        held_until=DATE_ADD(NOW(), INTERVAL {$holdMinutes} MINUTE), held_by=?
                    WHERE show_id=? AND seat_id=? AND version_number=?
                ");
                $upd->bind_param('iiiii', $newVer, $userId, $showId, $seatId, $row['version_number']);
                $upd->execute();
                if ($upd->affected_rows === 1) $held[] = $seatId;
                else $failed[] = $seatId;
            } else {
                $failed[] = $seatId;
            }
        }

        if (!empty($failed)) {
            $db->rollback();
            return ['ok' => false, 'failed' => $failed, 'msg' => 'Một số ghế vừa được người khác chọn.'];
        }
        $db->commit();
        return ['ok' => true, 'held' => $held];
    } catch (Throwable $e) {
        $db->rollback();
        return ['ok' => false, 'msg' => $e->getMessage()];
    }
}

// ─── Confirm booking ──────────────────────────────────────────────────────────
function confirmBooking(int $showId, array $seatIds, int $userId, int $pointsUsed = 0, int $pointsDiscount = 0): array {
    $db = db();
    $db->begin_transaction();
    try {
        $in     = implode(',', array_fill(0, count($seatIds), '?'));
        $types  = str_repeat('i', count($seatIds) + 2);
        $params = array_merge([$showId, $userId], $seatIds);

        $stmt = $db->prepare("
            SELECT ss.seat_id, s.type, ss.status, ss.held_until
            FROM tblSeatStatus ss
            JOIN tblSeats s ON s.id = ss.seat_id
            WHERE ss.show_id=? AND ss.held_by=?
              AND ss.status='held' AND ss.held_until > NOW()
              AND ss.seat_id IN ($in)
        ");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($rows) !== count($seatIds)) {
            $db->rollback();
            return ['ok' => false, 'msg' => 'Phiên giữ ghế đã hết hạn. Vui lòng chọn lại.'];
        }

        $flash    = getFlashSale($showId);
        $subtotal = 0;
        $items    = [];

        foreach ($rows as $row) {
            $basePrice  = getSeatPrice($row['type']);
            $discPct    = 0;
            $finalPrice = $basePrice;
            $isFlash    = false;
            if ($flash && $row['type'] !== 'couple') {
                $discPct    = $flash['discount_pct'];
                $finalPrice = (int)round($basePrice * (1 - $discPct / 100));
                $isFlash    = true;
            }
            $subtotal += $finalPrice;
            $items[]   = [
                'seat_id' => $row['seat_id'], 'type' => $row['type'],
                'original_price' => $basePrice, 'price' => $finalPrice,
                'flash_sale_applied' => $isFlash ? 1 : 0, 'discount_pct' => $discPct,
            ];
        }

        $total  = max(0, $subtotal - $pointsDiscount);
        $qrCode = 'MC-' . strtoupper(bin2hex(random_bytes(6)));

        $stmt = $db->prepare("INSERT INTO tblBookings (user_id,show_id,total_price,payment_status,qr_code) VALUES(?,?,?,'paid',?)");
        $stmt->bind_param('iiis', $userId, $showId, $total, $qrCode);
        $stmt->execute();
        $bookingId = $db->insert_id;

        foreach ($items as $item) {
            $stmt = $db->prepare("INSERT INTO tblBookingItems (booking_id,seat_id,price,original_price,flash_sale_applied,discount_pct) VALUES(?,?,?,?,?,?)");
            $stmt->bind_param('iiiiii', $bookingId, $item['seat_id'], $item['price'], $item['original_price'], $item['flash_sale_applied'], $item['discount_pct']);
            $stmt->execute();

            $stmt = $db->prepare("UPDATE tblSeatStatus SET status='booked',held_by=NULL,held_until=NULL WHERE show_id=? AND seat_id=?");
            $stmt->bind_param('ii', $showId, $item['seat_id']);
            $stmt->execute();

            $pts = match($item['type']) { 'vip' => POINTS_VIP, 'couple' => POINTS_COUPLE, default => POINTS_STANDARD };
            $stmt = $db->prepare("UPDATE tblUsers SET total_points=total_points+? WHERE id=?");
            $stmt->bind_param('ii', $pts, $userId);
            $stmt->execute();

            $reason = "Đặt ghế {$item['type']} - Booking #{$bookingId}";
            $stmt = $db->prepare("INSERT INTO tblPointsLog (user_id,booking_id,points_delta,reason) VALUES(?,?,?,?)");
            $stmt->bind_param('iiis', $userId, $bookingId, $pts, $reason);
            $stmt->execute();
        }

        if ($pointsUsed > 0) {
            $stmt = $db->prepare("UPDATE tblUsers SET total_points=total_points-? WHERE id=? AND total_points>=?");
            $stmt->bind_param('iii', $pointsUsed, $userId, $pointsUsed);
            $stmt->execute();
            $neg = -$pointsUsed;
            $reason = "Đổi điểm giảm giá - Booking #{$bookingId}";
            $stmt = $db->prepare("INSERT INTO tblPointsLog (user_id,booking_id,points_delta,reason) VALUES(?,?,?,?)");
            $stmt->bind_param('iiis', $userId, $bookingId, $neg, $reason);
            $stmt->execute();
        }

        $db->commit();
        return [
            'ok' => true, 'booking_id' => $bookingId, 'qr_code' => $qrCode,
            'total' => $total, 'points_used' => $pointsUsed,
            'points_discount' => $pointsDiscount, 'items' => $items,
        ];
    } catch (Throwable $e) {
        $db->rollback();
        return ['ok' => false, 'msg' => $e->getMessage()];
    }
}

// ─── Auto-suggest adjacent seats ─────────────────────────────────────────────
function suggestSeats(int $showId, int $n): array {
    $seats = getSeatsLayout($showId);
    $byRow = [];
    foreach ($seats as $s) {
        if ($s['type'] === 'couple') continue;
        $byRow[$s['row']][] = $s;
    }

    $rowOrder = ['E','D','C','F','B','A'];
    $best = null; $bestScore = PHP_INT_MIN;

    foreach ($rowOrder as $row) {
        if (!isset($byRow[$row])) continue;
        $avail = array_values(array_filter($byRow[$row], fn($s) => $s['status'] === 'available'));
        if (count($avail) < $n) continue;

        for ($i = 0; $i <= count($avail) - $n; $i++) {
            $window = array_slice($avail, $i, $n);
            $nums   = array_column($window, 'number');
            if (max($nums) - min($nums) !== $n - 1) continue;
            $score = 100 - abs(array_sum($nums)/$n - 4.5) * 10;
            if (in_array($row, ['C','D','E'])) $score += 50;
            if ($score > $bestScore) { $bestScore = $score; $best = $window; }
        }
    }

    if ($best) return ['ok' => true, 'seats' => $best];
    if ($n > 1) {
        $sub = suggestSeats($showId, $n - 1);
        if ($sub['ok']) return array_merge($sub, ['fallback' => true, 'requested' => $n]);
    }
    return ['ok' => false, 'msg' => 'Không tìm được đủ ghế liền kề.'];
}