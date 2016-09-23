<?php

/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 9/23/16
 * Time: 2:46 PM
 * To change this template use File | Settings | File Templates.
 */
class ExcelSheetField extends FormField
{

	private $excel;
	private $downloadLink;

	private static $allowed_actions = array(
		'download'
	);

	public function __construct($name, $title, PHPExcel $excel)
	{
		$this->excel = $excel;
		parent::__construct($name, $title);


		Requirements::css('silverstripe-manifests/css/ExcelSheetField.css');
	}


	public function ExcelData()
	{
		if($this->excel) {
			$htmlWriter = new PHPExcel_Writer_HTML($this->excel);
			$htmlWriter->writeAllSheets();
			$html = $htmlWriter->generateSheetData();

			$html = str_replace('.' . BASE_PATH . '/', '', $html);

			return $html;
		}
	}

	public function Styles()
	{
		if($this->excel) {
			$htmlWriter = new PHPExcel_Writer_HTML($this->excel);
			$styles = $htmlWriter->buildCSS(false);

			$css = "";
			foreach($styles as $target => $style) {

				$css .= "\n#" . $this->ID() . " " . $target . " {\n";

				$pairs = array();
				foreach ($style as $property => $value) {
					$pairs[] = $property . ':' . $value;
				}
				$css .= implode(";\n ", $pairs);
				$css .= "}\n";

			}

			return $css;
		}
	}

	public function setDownloadLink($link)
	{
		$this->downloadLink = $link;
		return $this;
	}

	public function DownloadLink()
	{

		if($this->downloadLink){
			return $this->downloadLink;
		}

		$uri = $_SERVER['REQUEST_URI'];
		$query = '';
		if(strpos($uri, '?') !== false) {
			$query = substr($uri, strpos($uri, '?'));
		}

		return HTTP::setGetVar('download', 1, $this->Link('download') . $query);
	}


	public function download()
	{
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

		$title = $this->excel->getProperties()->getTitle();

		$fileName = str_replace(' ', '-', $title) . date('-Y-m-d-h-i-a', strtotime(SS_Datetime::now()->getValue()));
		header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header ('Expires: Mon, 26 Jul 1990 00:00:00 GMT');
		header ('Last-Modified: '.date('D, d M Y H:i:s', strtotime(SS_Datetime::now()->getValue())).' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate');
		header ('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

}