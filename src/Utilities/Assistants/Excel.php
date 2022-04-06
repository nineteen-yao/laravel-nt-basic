<?php
/**
 * Excel.php
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/9/30 10:08
 */

namespace YLarNtBasic\Utilities\Assistants;


use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WXlsx;
use Ynineteen\Supports\Logger;

class Excel
{
    /**
     * 加载一个电子表格
     *
     * @param string $file
     * @param array|string $sheetFilter
     * @return Spreadsheet|null
     */
    public static function load(string $file, $sheetFilter = [])
    {
        static $xlsx;

        $sheetFilter = is_string($sheetFilter) ? [$sheetFilter] : $sheetFilter;

        $md5name = md5($file . json_encode($sheetFilter));
        if (empty($xlsx[$md5name])) {
            $reader = new Xlsx();
            if ($sheetFilter) {
                $reader->setLoadSheetsOnly($sheetFilter);
            }
            $xlsx[$md5name] = $reader->load($file);
        }

        return $xlsx[$md5name];
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param int $index
     * @return Worksheet
     * @throws Exception
     */
    public static function getWorkSheet(Spreadsheet $spreadsheet, $index = 0)
    {
        $spreadsheet->setActiveSheetIndex($index);

        return $spreadsheet->getActiveSheet();
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @return \PhpOffice\PhpSpreadsheet\Writer\Xlsx
     */
    public static function writer(Spreadsheet $spreadsheet)
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setOffice2003Compatibility(true);

        return $writer;
    }

    /**
     * 读取数据,如果PhpSpreadsheet组件读取有异常，那么采用PHPEXcel来读取
     *
     * @param string $file 表格文件路径
     * @param bool $hasTitle 表格是否含有表头
     * @param int $sheetIndex 读取的工作表索引号
     * @param array $itemHash 列对应的字段映射数组
     * @return array
     */
    public static function readData(string $file, $hasTitle = true, $sheetIndex = 0, $itemHash = [])
    {
        try {
            $reader = new Xlsx();
            $reader->setReadDataOnly(true);
            $xlsx = $reader->load($file);
            $xlsx->setActiveSheetIndex($sheetIndex);
            $sheet = $xlsx->getActiveSheet();

            //总行数,列数
            $maxRowNumber = (integer)$sheet->getHighestRow();
            $maxColName = $sheet->getHighestColumn();
            $maxColIndex = Coordinate::columnIndexFromString($maxColName);

            $offset = $hasTitle ? 2 : 1;
            $rows = $maxRowNumber - $offset;
            //没有数据
            if ($rows <= 0) {
                return [];
            }

            $data = [];
            $rowIndex = 0;
            for ($row = $offset; $row <= $maxRowNumber; $row++) {
                for ($col = 1; $col <= $maxColIndex; $col++) {
                    $hashName = $itemHash[$col - 1] ?? ($col - 1);    //让数据的索引值从0 开始
                    $data[$rowIndex][$hashName] = trim($sheet->getCellByColumnAndRow($col, $row)->getValue());
                }

                $rowIndex++;
            }


            return $data;
        } catch (\Throwable $throwable) {
            try {
                return static::readDataByPHPExcel($file, $hasTitle, $sheetIndex, $itemHash);
            } catch (\Throwable $exception) {
                //发生异常，使用PHPOffice/PHPExcel组件实现
                Logger::error('读取Excel数据错误', $file, $throwable, $exception);

                return [];
            }
        }
    }

    /**
     * 通过以前的PHPExcel组件来读取数据
     *
     * @param string $file xls文件路径
     * @param bool $hasTitle 表格是否含有标题行
     * @param int $sheetIndex sheet的索引号
     * @param array $itemHash 每列的哈希对应
     * @return array|bool|int
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public static function readDataByPHPExcel(string $file, $hasTitle = true, $sheetIndex = 0, $itemHash = [])
    {
        $inputFileType = PHPExcel_IOFactory::identify($file);
        $reader = PHPExcel_IOFactory::createReader($inputFileType);
        $excel = $reader->load($file);

        $sheetCount = $excel->getSheetCount();
        $maxSheetIndex = $sheetCount - 1;

        if ($maxSheetIndex < $sheetIndex) {
            return Logger::error("工作表未找到！", 'Excel文件路径：' . $file);
        }

        $worksheet = $excel->getSheet($sheetIndex);

        $rows = $worksheet->toArray();

        $data = [];
        foreach ($rows as $key => $row) {
            if ($hasTitle && $key === 0) continue;

            $tempRow = [];
            foreach ($row as $col => $value) {
                $hashName = $itemHash[$col] ?? $col;
                $tempRow[$hashName] = trim($value);
            }
            $data[] = $tempRow;
        }

        return $data;
    }

    /**
     * 时间格式
     *
     * @param $time
     * @return bool|float
     */
    public static function formatDate($time)
    {
        return Date::PHPToExcel($time);
    }

    /**
     * 复制多行
     *
     * @param Worksheet $sheet
     * @param                $srcRange
     * @param                $dstCell
     * @param Worksheet|null $destSheet
     * @throws Exception
     */
    public static function copyRows(Worksheet $sheet, $srcRange, $dstCell, Worksheet $destSheet = null)
    {

        if (!isset($destSheet)) {
            $destSheet = $sheet;
        }

        if (!preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $srcRange, $srcRangeMatch)) {
            return;
        }

        if (!preg_match('/^([A-Z]+)(\d+)$/', $dstCell, $destCellMatch)) {
            // Invalid dest cell
            return;
        }

        $srcColumnStart = $srcRangeMatch[1];
        $srcRowStart = $srcRangeMatch[2];
        $srcColumnEnd = $srcRangeMatch[3];
        $srcRowEnd = $srcRangeMatch[4];

        $destColumnStart = $destCellMatch[1];
        $destRowStart = $destCellMatch[2];

        $srcColumnStart = Coordinate::columnIndexFromString($srcColumnStart);
        $srcColumnEnd = Coordinate::columnIndexFromString($srcColumnEnd);
        $destColumnStart = Coordinate::columnIndexFromString($destColumnStart);

        $rowCount = 0;
        for ($row = $srcRowStart; $row <= $srcRowEnd; $row++) {
            $colCount = 0;
            for ($col = $srcColumnStart; $col <= $srcColumnEnd; $col++) {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $style = $sheet->getStyleByColumnAndRow($col, $row);
                $dstCell = Coordinate::stringFromColumnIndex($destColumnStart + $colCount) . (string)($destRowStart + $rowCount);
                $destSheet->setCellValue($dstCell, $cell->getValue());
                $destSheet->duplicateStyle($style, $dstCell);

                // Set width of column, but only once per column
                if ($rowCount === 0) {
                    $w = $sheet->getColumnDimensionByColumn($col)->getWidth();
                    $destSheet->getColumnDimensionByColumn($destColumnStart + $colCount)->setAutoSize(false);
                    $destSheet->getColumnDimensionByColumn($destColumnStart + $colCount)->setWidth($w);
                }

                $colCount++;
            }

            $h = $sheet->getRowDimension($row)->getRowHeight();
            $destSheet->getRowDimension($destRowStart + $rowCount)->setRowHeight($h);

            $rowCount++;
        }

        foreach ($sheet->getMergeCells() as $mergeCell) {
            $mc = explode(":", $mergeCell);
            $mergeColSrcStart = Coordinate::columnIndexFromString(preg_replace("/[0-9]*/", "", $mc[0]));
            $mergeColSrcEnd = Coordinate::columnIndexFromString(preg_replace("/[0-9]*/", "", $mc[1]));
            $mergeRowSrcStart = ((int)preg_replace("/[A-Z]*/", "", $mc[0]));
            $mergeRowSrcEnd = ((int)preg_replace("/[A-Z]*/", "", $mc[1]));

            $relativeColStart = $mergeColSrcStart - $srcColumnStart;
            $relativeColEnd = $mergeColSrcEnd - $srcColumnStart;
            $relativeRowStart = $mergeRowSrcStart - $srcRowStart;
            $relativeRowEnd = $mergeRowSrcEnd - $srcRowStart;

            if (0 <= $mergeRowSrcStart && $mergeRowSrcStart >= $srcRowStart && $mergeRowSrcEnd <= $srcRowEnd) {
                $targetColStart = Coordinate::stringFromColumnIndex($destColumnStart + $relativeColStart);
                $targetColEnd = Coordinate::stringFromColumnIndex($destColumnStart + $relativeColEnd);
                $targetRowStart = $destRowStart + $relativeRowStart;
                $targetRowEnd = $destRowStart + $relativeRowEnd;

                $merge = (string)$targetColStart . (string)($targetRowStart) . ":" . (string)$targetColEnd . (string)($targetRowEnd);
                $destSheet->mergeCells($merge);
            }
        }
    }

    /**
     * @param Spreadsheet $sourceSheet
     * @param Spreadsheet $destSheet
     */
    public static function copyStyleXFCollection(Spreadsheet $sourceSheet, Spreadsheet $destSheet)
    {
        $collection = $sourceSheet->getCellXfCollection();

        foreach ($collection as $key => $item) {
            $destSheet->addCellXf($item);
        }
    }

    /**
     * @param Worksheet $sheet
     * @param           $srcRange
     * @param           $dstCell
     * @throws Exception
     */
    public static function copyRange(Worksheet $sheet, $srcRange, $dstCell)
    {
        // Validate source range. Examples: A2:A3, A2:AB2, A27:B100
        if (!preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $srcRange, $srcRangeMatch)) {
            // Wrong source range
            return;
        }
        // Validate destination cell. Examples: A2, AB3, A27
        if (!preg_match('/^([A-Z]+)(\d+)$/', $dstCell, $destCellMatch)) {
            // Wrong destination cell
            return;
        }

        $srcColumnStart = $srcRangeMatch[1];
        $srcRowStart = $srcRangeMatch[2];
        $srcColumnEnd = $srcRangeMatch[3];
        $srcRowEnd = $srcRangeMatch[4];

        $destColumnStart = $destCellMatch[1];
        $destRowStart = $destCellMatch[2];

        // For looping purposes we need to convert the indexes instead
        // Note: We need to subtract 1 since column are 0-based and not 1-based like this method acts.

        $srcColumnStart = Cell::columnIndexFromString($srcColumnStart) - 1;
        $srcColumnEnd = Cell::columnIndexFromString($srcColumnEnd) - 1;
        $destColumnStart = Cell::columnIndexFromString($destColumnStart) - 1;

        $rowCount = 0;
        for ($row = $srcRowStart; $row <= $srcRowEnd; $row++) {
            $colCount = 0;
            for ($col = $srcColumnStart; $col <= $srcColumnEnd; $col++) {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $style = $sheet->getStyleByColumnAndRow($col, $row);
                $dstCell = Cell::stringFromColumnIndex($destColumnStart + $colCount) . (string)($destRowStart + $rowCount);
                $sheet->setCellValue($dstCell, $cell->getValue());
                $sheet->duplicateStyle($style, $dstCell);

                // Set width of column, but only once per row
                if ($rowCount === 0) {
                    $w = $sheet->getColumnDimensionByColumn($col)->getWidth();
                    $sheet->getColumnDimensionByColumn($destColumnStart + $colCount)->setAutoSize(false);
                    $sheet->getColumnDimensionByColumn($destColumnStart + $colCount)->setWidth($w);
                }

                $colCount++;
            }

            $h = $sheet->getRowDimension($row)->getRowHeight();
            $sheet->getRowDimension($destRowStart + $rowCount)->setRowHeight($h);

            $rowCount++;
        }

        foreach ($sheet->getMergeCells() as $mergeCell) {
            $mc = explode(":", $mergeCell);
            $mergeColSrcStart = Cell::columnIndexFromString(preg_replace("/[0-9]*/", "", $mc[0])) - 1;
            $mergeColSrcEnd = Cell::columnIndexFromString(preg_replace("/[0-9]*/", "", $mc[1])) - 1;
            $mergeRowSrcStart = ((int)preg_replace("/[A-Z]*/", "", $mc[0]));
            $mergeRowSrcEnd = ((int)preg_replace("/[A-Z]*/", "", $mc[1]));

            $relativeColStart = $mergeColSrcStart - $srcColumnStart;
            $relativeColEnd = $mergeColSrcEnd - $srcColumnStart;
            $relativeRowStart = $mergeRowSrcStart - $srcRowStart;
            $relativeRowEnd = $mergeRowSrcEnd - $srcRowStart;

            if (0 <= $mergeRowSrcStart && $mergeRowSrcStart >= $srcRowStart && $mergeRowSrcEnd <= $srcRowEnd) {
                $targetColStart = Cell::stringFromColumnIndex($destColumnStart + $relativeColStart);
                $targetColEnd = Cell::stringFromColumnIndex($destColumnStart + $relativeColEnd);
                $targetRowStart = $destRowStart + $relativeRowStart;
                $targetRowEnd = $destRowStart + $relativeRowEnd;

                $merge = (string)$targetColStart . (string)($targetRowStart) . ":" . (string)$targetColEnd . (string)($targetRowEnd);
                //Merge target cells
                $sheet->mergeCells($merge);
            }
        }
    }

    /**
     * 写入数据到表格
     *
     * @param string $file 表格存储路径
     * @param array $rows 存储的数据
     * @param array $numberCols 数据类型为数字的列索引
     * @param bool $headBold 标题是否加黑
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function writeData(string $file, array $rows, $numberCols = [], $headBold = true): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rowIndex = 0;
        foreach ($rows as $item) {
            $colIndex = 0;
            foreach ($item as $value) {
                $coord = static::getCellCoordinate($colIndex, $rowIndex, $sheet);
                $sheet->setCellValue($coord, trim($value));
                if (!in_array($colIndex, $numberCols)) {
                    $sheet->getCell($coord)->setDataType(DataType::TYPE_STRING);
                }
                if ($headBold && $rowIndex === 0) {
                    $sheet->getStyle($coord)->applyFromArray([
                        'font' => [
                            'bold' => true
                        ]
                    ]);
                }

                $colIndex++;
            }
            $rowIndex++;
        }


        $writer = new WXlsx($spreadsheet);
        $writer->save($file);
    }

    /**
     * 获取坐标值
     *
     * @param int $colIndex
     * @param int $rowIndex
     * @param null|Worksheet $sheet
     * @return string
     */
    public static function getCellCoordinate(int $colIndex, int $rowIndex, $sheet = null): string
    {
        if ($sheet === null) {
            $sheet = (new  Spreadsheet())->getActiveSheet();
        }

        return $sheet->getCellByColumnAndRow(++$colIndex, ++$rowIndex)->getCoordinate();
    }

}
