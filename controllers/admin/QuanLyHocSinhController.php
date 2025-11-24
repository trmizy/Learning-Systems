<?php
require_once __DIR__ . '/../../models/admin/QuanLyHocSinhModel.php';

class QuanLyHocSinhController
{
    protected $db;
    protected $model;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->model = new QuanLyHocSinhModel($db);
    }

    public function index()
    {
        // Prepare view variables
        $isSearch = isset($_GET['search']) && $_GET['search'] == '1';
        $q_maHS = trim($_GET['maHS'] ?? '');
        $q_hoTen = trim($_GET['hoTen'] ?? '');
        $q_maLop = trim($_GET['maLop'] ?? '');
        $q_khoi = trim($_GET['khoi'] ?? '');

        $students = [];
        if ($isSearch) {
            $criteria = [];
            if ($q_maHS !== '') $criteria['maHS'] = $q_maHS;
            if ($q_hoTen !== '') $criteria['hoTen'] = $q_hoTen;
            if ($q_maLop !== '') $criteria['maLop'] = $q_maLop;
            if ($q_khoi !== '') $criteria['khoi'] = $q_khoi;

            $students = $this->model->search($criteria);
        }

        // Expose variables to the view
        $isSearch = $isSearch;
        $q_maHS = $q_maHS;
        $q_hoTen = $q_hoTen;
        $q_maLop = $q_maLop;
        $q_khoi = $q_khoi;

        require __DIR__ . '/../../views/admin/quanLyHocSinh/quanLyHocSinhView.php';
    }

    public function view()
    {
        $maHS = $_GET['maHS'] ?? null;
        if (!$maHS) {
            http_response_code(400);
            echo 'maHS required';
            exit;
        }
        $student = $this->model->find($maHS);
        require __DIR__ . '/../../views/admin/quanLyHocSinh/view.php';
    }

    public function edit()
    {
        $maHS = $_GET['maHS'] ?? $_POST['maHS'] ?? null;
        if (!$maHS) {
            http_response_code(400);
            echo 'maHS required';
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [];
            $data['hoTen'] = $_POST['hoTen'] ?? null;
            $data['ngaySinh'] = $_POST['ngaySinh'] ?? null;
            $data['diaChi'] = $_POST['diaChi'] ?? null;
            $data['email'] = $_POST['email'] ?? null;
            $data['sdt'] = $_POST['sdt'] ?? null;
            $data['ph_sdt'] = $_POST['ph_sdt'] ?? null;
            $data['loaiHanhKiem'] = $_POST['loaiHanhKiem'] ?? null;

            try {
                $this->model->update($maHS, $data);
                header('Location: /modules/quanLyHocSinh/view.php?maHS=' . urlencode($maHS) . '&updated=1');
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        $student = $this->model->find($maHS);
        require __DIR__ . '/../../views/admin/quanLyHocSinh/edit.php';
    }
}
