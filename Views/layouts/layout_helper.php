<?php

/**
 * Layout Helper Functions
 * Các hàm hỗ trợ sử dụng layout
 */

/**
 * Render layout với content
 * @param string $content Nội dung trang
 * @param string $page_title Tiêu đề trang
 */
function renderLayout($content, $page_title = 'Hệ thống Quản lý Bệnh viện')
{
    global $page_title;
    $page_title = $page_title;

    // Start output buffering
    ob_start();

    // Include main layout
    include 'Views/layouts/main_layout.php';

    // Get the buffered content
    $layout_content = ob_get_clean();

    // Replace content placeholder
    $layout_content = str_replace('<?php echo $content ?? \'\'; ?>', $content, $layout_content);

// Output the final layout
echo $layout_content;
}

/**
* Render chỉ sidebar cho từng role
* @param string $role Role của user (admin, doctor, patient)
*/
function renderSidebar($role)
{
switch ($role) {
case 'admin':
include 'Views/layouts/admin_sidebar.php';
break;
case 'doctor':
include 'Views/layouts/doctor_sidebar.php';
break;
case 'patient':
include 'Views/layouts/patient_sidebar.php';
break;
default:
echo '<div class="alert alert-danger">Role không hợp lệ!</div>';
}
}

/**
* Render chỉ header
*/
function renderHeader()
{
include 'Views/layouts/header.php';
}

/**
* Render chỉ footer
*/
function renderFooter()
{
include 'Views/layouts/footer.php';
}

/**
* Kiểm tra và set active menu item
* @param string $current_page Trang hiện tại
* @param string $menu_item Menu item cần check
* @return string CSS class
*/
function isActiveMenu($current_page, $menu_item)
{
return ($current_page === $menu_item) ? 'active' : '';
}

/**
* Kiểm tra URL hiện tại có chứa pattern không
* @param string $pattern Pattern cần tìm
* @return string CSS class
*/
function isActiveUrl($pattern)
{
$current_url = $_SERVER['REQUEST_URI'] ?? '';
return (strpos($current_url, $pattern) !== false) ? 'active' : '';
}

/**
* Lấy role text từ role code
* @param string $role Role code
* @return string Role text
*/
function getRoleText($role)
{
switch ($role) {
case 'admin':
return 'Quản trị viên';
case 'doctor':
return 'Bác sĩ';
case 'patient':
return 'Bệnh nhân';
default:
return 'Không xác định';
}
}

/**
* Lấy role badge class
* @param string $role Role code
* @return string CSS class
*/
function getRoleBadgeClass($role)
{
switch ($role) {
case 'admin':
return 'role-admin';
case 'doctor':
return 'role-doctor';
case 'patient':
return 'role-patient';
default:
return '';
}
}