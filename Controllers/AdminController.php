<?php

require_once 'Models/Admin.php';
require_once 'Models/Doctor.php';

class AdminController
{
    private $auth;
    private $adminModel;
    private $doctorModel;

    public function __construct()
    {
        $this->auth = new AuthController();
        $this->adminModel = new Admin();
        $this->doctorModel = new Doctor();
    }

    /**
     * Dashboard admin
     */
    public function dashboard()
    {
        $this->auth->requireAuth('admin');

        // Lấy thống kê tổng quan
        $stats = $this->getDashboardStats();

        $page_title = 'Dashboard Admin';

        // Start output buffering để lấy content
        ob_start();
        include 'Views/admin/dashboard.php';
        $content = ob_get_clean();

        // Sử dụng renderLayout function
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Quản lý lịch làm việc bác sĩ
     */
    public function manageDoctorSchedules()
    {
        $this->auth->requireAuth('admin');

        // Lấy danh sách tất cả bác sĩ
        $doctors = $this->doctorModel->getAll();

        // Lấy lịch làm việc của tất cả bác sĩ
        $allSchedules = [];
        $scheduleStats = [
            'total_schedules' => 0,
            'active_schedules' => 0,
            'morning_shifts' => 0,
            'afternoon_shifts' => 0,
            'evening_shifts' => 0
        ];

        foreach ($doctors as $doctor) {
            $schedules = $this->doctorModel->getSchedules($doctor['id']);
            $allSchedules[$doctor['id']] = [
                'doctor' => $doctor,
                'schedules' => $schedules
            ];

            // Cập nhật thống kê
            $scheduleStats['total_schedules'] += count($schedules);
            foreach ($schedules as $schedule) {
                if ($schedule['trang_thai'] === 'active') {
                    $scheduleStats['active_schedules']++;
                }
                switch ($schedule['loai_ca']) {
                    case 'Ca sáng':
                        $scheduleStats['morning_shifts']++;
                        break;
                    case 'Ca chiều':
                        $scheduleStats['afternoon_shifts']++;
                        break;
                    case 'Ca tối':
                        $scheduleStats['evening_shifts']++;
                        break;
                }
            }
        }

        // Nhóm lịch theo ngày
        $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
        $schedulesByDay = [];

        foreach ($daysOfWeek as $day) {
            $schedulesByDay[$day] = [];
            foreach ($allSchedules as $doctorId => $data) {
                $daySchedules = $this->doctorModel->getSchedulesByDay($doctorId, $day);
                if (!empty($daySchedules)) {
                    $schedulesByDay[$day][] = [
                        'doctor' => $data['doctor'],
                        'schedules' => $daySchedules
                    ];
                }
            }
        }

        $page_title = 'Quản lý lịch làm việc bác sĩ';

        // Start output buffering để lấy content
        ob_start();
        include 'Views/admin/doctor_schedules.php';
        $content = ob_get_clean();

        // Sử dụng renderLayout function
        require_once 'Views/layouts/layout_helper.php';
        renderLayout($content, $page_title);
    }

    /**
     * Lấy thống kê dashboard
     */
    private function getDashboardStats()
    {
        // Đây là placeholder, bạn có thể implement logic thống kê thực tế
        return [
            'total_doctors' => 10,
            'total_patients' => 150,
            'total_appointments' => 45,
            'total_revenue' => 50000000
        ];
    }
}