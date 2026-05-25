<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/../../app/models/DashboardModel.php';

class DashboardController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new DashboardModel($conn);
    }

    public function index()
    {
        $statistik      = $this->model->getStatistik();
        $barang_terbaru = $this->model->getBarangTerbaru();
        $barang_kritis  = $this->model->getBarangKritis();

        global $conn;

        require_once __DIR__ . '/../../app/views/dashboard/index.php';
    }
}
