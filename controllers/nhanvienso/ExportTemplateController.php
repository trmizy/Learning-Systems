<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportTemplateController {
    
    /**
     * Xuất file Excel mẫu điểm tuyển sinh
     */
    public function exportTemplate() {
        // Kiểm tra quyền
        require_role(['nhanvienso']);
        
        try {
            // Tạo Spreadsheet mới
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Điểm Tuyển Sinh');
            
            // === HEADER (Dòng 1) ===
            $headers = [
                'A1' => 'Mã Thí Sinh',
                'B1' => 'Họ Tên',
                'C1' => 'CCCD',
                'D1' => 'Ngày Sinh',
                'E1' => 'Điểm Văn',
                'F1' => 'Điểm Toán',
                'G1' => 'Điểm Anh',
                'H1' => 'Số Điện Thoại',
                'I1' => 'Nơi Sinh',
                'J1' => 'Giới Tính'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Style header
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
            $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
            
            // === DỮ LIỆU MẪU (5 dòng) ===
            $sampleData = [
                ['TS258188103', 'Dương Quốc Việt', '076974215422', '07/04/2011', 1.2, 6.0, 1.7, '03876798520', 'Đồng Nai', 'Nam'],
                ['TS258188104', 'Nguyễn Thị Mai', '079012345678', '15/03/2011', 8.5, 9.0, 7.5, '0901234567', 'TP.HCM', 'Nữ'],
                ['TS258188105', 'Trần Văn An', '079087654321', '22/08/2011', 7.0, 8.0, 6.5, '0912345678', 'Bình Dương', 'Nam'],
                ['TS258188106', 'Lê Thị Hoa', '079098765432', '10/12/2011', 9.0, 8.5, 8.0, '0923456789', 'Long An', 'Nữ'],
                ['TS258188107', 'Phạm Minh Tuấn', '079045678901', '05/06/2011', 6.5, 7.0, 7.5, '0934567890', 'Vũng Tàu', 'Nam']
            ];
            
            $row = 2;
            foreach ($sampleData as $data) {
                $sheet->fromArray($data, null, 'A' . $row);
                $row++;
            }
            
            // Style dữ liệu
            $dataStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ];
            $sheet->getStyle('A2:J6')->applyFromArray($dataStyle);
            
            // Căn giữa cột điểm
            $sheet->getStyle('E2:G6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Format cột ngày sinh
            $sheet->getStyle('D2:D6')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            
            // === SET WIDTH CỘT ===
            $sheet->getColumnDimension('A')->setWidth(15);  // Mã thí sinh
            $sheet->getColumnDimension('B')->setWidth(25);  // Họ tên
            $sheet->getColumnDimension('C')->setWidth(15);  // CCCD
            $sheet->getColumnDimension('D')->setWidth(12);  // Ngày sinh
            $sheet->getColumnDimension('E')->setWidth(12);  // Điểm Văn
            $sheet->getColumnDimension('F')->setWidth(12);  // Điểm Toán
            $sheet->getColumnDimension('G')->setWidth(12);  // Điểm Anh
            $sheet->getColumnDimension('H')->setWidth(15);  // SĐT
            $sheet->getColumnDimension('I')->setWidth(20);  // Nơi sinh
            $sheet->getColumnDimension('J')->setWidth(12);  // Giới tính
            
            // Set height dòng
            $sheet->getRowDimension(1)->setRowHeight(25);
            for ($i = 2; $i <= 6; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(20);
            }
            
            // === GHI CHÚ PHÍA DƯỚI ===
            $sheet->setCellValue('A8', 'GHI CHÚ:');
            $sheet->setCellValue('A9', '- Dòng đầu tiên (tiêu đề) sẽ bị bỏ qua khi import');
            $sheet->setCellValue('A10', '- Giới tính phải là "Nam" hoặc "Nữ" (KHÔNG ĐƯỢC ĐỂ SỐ)');
            $sheet->setCellValue('A11', '- Ngày sinh format: dd/mm/yyyy (VD: 07/04/2011)');
            $sheet->setCellValue('A12', '- CCCD phải là 12 chữ số');
            $sheet->setCellValue('A13', '- Điểm Văn, Toán, Anh: 0-10 (VD: 8.5)');
            $sheet->setCellValue('A14', '- Công thức tổng điểm: (Toán × 2) + (Văn × 2) + Anh');
            
            $sheet->getStyle('A8:A14')->getFont()->setItalic(true)->setSize(10);
            $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(11);
            
            // === XUẤT FILE ===
            $fileName = 'mau_diem_tuyen_sinh.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            error_log("Error exportTemplate: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Không thể tạo file mẫu: ' . $e->getMessage();
            header('Location: /public/index.php?action=nhanvienso-tuyen-sinh-upload');
            exit;
        }
    }
}
