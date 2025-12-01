<?php

/**
 * Pagination Helper
 * Dùng để render phân trang Bootstrap, có thể tái sử dụng cho nhiều bảng khác nhau.
 *
 * @param int    $currentPage  Trang hiện tại (1-based)
 * @param int    $totalPages   Tổng số trang
 * @param string $baseUrl      URL cơ bản (ví dụ: ./patient_appointments)
 * @param array  $queryParams  Các query param khác cần giữ lại (search, filters, ...)
 */
function renderPagination($currentPage, $totalPages, $baseUrl, array $queryParams = [])
{
    $currentPage = max(1, (int) $currentPage);
    $totalPages  = max(1, (int) $totalPages);

    // Nếu không có trang hợp lệ thì bỏ qua
    if ($totalPages < 1) {
        return;
    }

    // Loại bỏ param rỗng
    $queryParams = array_filter(
        $queryParams,
        static function ($v) {
            return $v !== null && $v !== '';
        }
    );

    // Hàm build URL với page + query giữ lại filter
    $buildUrl = static function ($page) use ($baseUrl, $queryParams) {
        $params          = $queryParams;
        $params['page']  = (int) $page;
        $queryString     = http_build_query($params);
        return $baseUrl . ($queryString ? ('?' . $queryString) : '');
    };

    echo '<nav aria-label="Pagination" class="mt-3">';
    echo '<ul class="pagination justify-content-center mb-0">';

    // Previous
    $prevDisabled = $currentPage <= 1 ? ' disabled' : '';
    echo '<li class="page-item' . $prevDisabled . '">';
    $prevHref = $currentPage > 1 ? $buildUrl($currentPage - 1) : '#';
    echo '<a class="page-link" href="' . htmlspecialchars($prevHref) . '" aria-label="Trang trước">';
    echo '<span aria-hidden="true">&laquo;</span>';
    echo '</a>';
    echo '</li>';

    // Cửa sổ hiển thị trang (ví dụ: 1 ... 3 4 [5] 6 7 ... 20)
    $window = 2;
    $start  = max(1, $currentPage - $window);
    $end    = min($totalPages, $currentPage + $window);

    if ($start > 1) {
        // Trang 1
        $active = $currentPage === 1 ? ' active' : '';
        echo '<li class="page-item' . $active . '">';
        echo '<a class="page-link" href="' . htmlspecialchars($buildUrl(1)) . '">1</a>';
        echo '</li>';

        if ($start > 2) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i === 1 || $i === $totalPages) {
            // Đã render ở trên hoặc sẽ render ở dưới
            continue;
        }
        $active = $i === $currentPage ? ' active' : '';
        echo '<li class="page-item' . $active . '">';
        echo '<a class="page-link" href="' . htmlspecialchars($buildUrl($i)) . '">' . $i . '</a>';
        echo '</li>';
    }


    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Trang cuối
        $active = $currentPage === $totalPages ? ' active' : '';
        echo '<li class="page-item' . $active . '">';
        echo '<a class="page-link" href="' . htmlspecialchars($buildUrl($totalPages)) . '">' . $totalPages . '</a>';
        echo '</li>';
    }

    // Next
    $nextDisabled = $currentPage >= $totalPages ? ' disabled' : '';
    echo '<li class="page-item' . $nextDisabled . '">';
    $nextHref = $currentPage < $totalPages ? $buildUrl($currentPage + 1) : '#';
    echo '<a class="page-link" href="' . htmlspecialchars($nextHref) . '" aria-label="Trang sau">';
    echo '<span aria-hidden="true">&raquo;</span>';
    echo '</a>';
    echo '</li>';

    echo '</ul>';
    echo '</nav>';
}