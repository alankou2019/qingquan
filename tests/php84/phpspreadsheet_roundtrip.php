<?php

$projectRoot = dirname(__DIR__, 2);
$autoload = $projectRoot . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "FAIL: run composer install before this test\n");
    exit(1);
}
require_once $autoload;

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue(array(1, 1), '员工编号');
$sheet->setCellValueExplicit(
    array(1, 2),
    '00125',
    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
);
$sheet->setCellValue(array(2, 1), '应发工资');
$sheet->setCellValue(array(2, 2), 12345.67);

$temporaryFile = tempnam(sys_get_temp_dir(), 'wecom-xlsx-');
if ($temporaryFile === false) {
    fwrite(STDERR, "FAIL: cannot create temporary spreadsheet\n");
    exit(1);
}
$xlsxFile = $temporaryFile . '.xlsx';
unlink($temporaryFile);

try {
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($xlsxFile);
    $spreadsheet->disconnectWorksheets();

    $loaded = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsxFile);
    $loadedSheet = $loaded->getActiveSheet();
    if ($loadedSheet->getCell(array(1, 2))->getValue() !== '00125') {
        throw new \RuntimeException('employee number changed');
    }
    if (abs((float) $loadedSheet->getCell(array(2, 2))->getValue() - 12345.67) > 0.001) {
        throw new \RuntimeException('salary amount changed');
    }
    $loaded->disconnectWorksheets();
} catch (\Throwable $e) {
    fwrite(STDERR, "FAIL: " . $e->getMessage() . "\n");
    exit(1);
} finally {
    if (is_file($xlsxFile)) {
        unlink($xlsxFile);
    }
}

fwrite(STDOUT, "PASS: PhpSpreadsheet write/read round trip\n");
