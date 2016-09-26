<?php

/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 9/23/16
 * Time: 1:40 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class ManifestReport extends ViewableData
{

	private static $title = 'Manifest';

	private static $logo_file = 'silverstripe-manifests/images/header-image.png';
	private static $logo_height = 80;


	protected $excel = null;

	public function getTitle()
	{
		return self::config()->get('title');
	}

	public static function get_manifests()
	{
		$classes =  ClassInfo::subclassesFor(get_called_class());
		$manifests = array();
		foreach ($classes as $class) {
			$reflectionClass = new ReflectionClass($class);
			if ($reflectionClass->isAbstract()) continue;

			$manifests[$class] = new $class();
		}
		return $manifests;


	}

	public function canView($member = null) {
		if(!$member && $member !== FALSE) {
			$member = Member::currentUser();
		}

		$extended = $this->extendedCan('canView', $member);
		if($extended !== null) {
			return $extended;
		}

		if($member && Permission::checkMember($member, array('CMS_ACCESS_LeftAndMain', 'CMS_ACCESS_ManifestAdmin'))) {
			return true;
		}

		return false;
	}

	public function extendedCan($methodName, $member) {
		$results = $this->extend($methodName, $member);
		if($results && is_array($results)) {
			$results = array_filter($results, function($v) {return !is_null($v);});
			if($results) return min($results);
		}
		return null;
	}


	public function getCMSFields() {
		$fields = new FieldList();

		if(method_exists($this, 'parameterFields') && $parameterFields = $this->parameterFields()) {
			foreach($parameterFields as $field) {
				$field->setName(sprintf('filters[%s]', $field->getName()));
				$field->addExtraClass('no-change-track');
				$fields->push($field);
			}
			$fields->push(new FormAction('updatereport', _t('GridField.Filter')));
		}

		$this->setReportData();

		if($this->excel) {
			$uri = $_SERVER['REQUEST_URI'];
			$query = '?';
			if(strpos($uri, '?') !== false) {
				$query = substr($uri, strpos($uri, '?'));
			}
			$query .= '&Manifest=' . get_class($this);
			$fields->push(ExcelSheetField::create('Data', 'Manifest', $this->excel)->setDownloadLink('admin/manifests/EditForm/field/Data/download/' . $query));
		}

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	public function getCMSValidator()
	{
		$validator = new RequiredFields();
		$this->extend('updateCMSValidator', $validator);
		return $validator;
	}


	public function getLink($action = null) {
		return Controller::join_links(
			'admin/manifests/',
			"$this->class",
			'/', // trailing slash needed if $action is null!
			"$action"
		);
	}


	abstract function setReportData();


	public function getDocument()
	{
		if($this->excel) {
			return $this->excel;
		}
		$this->excel = new PHPExcel();
		$this->excel->getProperties()->setCreator(Member::currentUser()->getName());
		$this->excel->getProperties()->setLastModifiedBy(Member::currentUser()->getName());
		$this->excel->getProperties()->setTitle($this->getTitle());
		$this->excel->getProperties()->setSubject($this->getTitle());
		$this->excel->getProperties()->setDescription($this->getTitle());
		return $this->excel;
	}

	public function getSheetCode($sheetName)
	{
		return substr(md5($sheetName), 0, 4);
	}


	public function setSheetHeader($sheet)
	{

		$path = BASE_PATH . '/' . Config::inst()->get('ManifestReport', 'logo_file');
		if($path && file_exists($path)) {
			$image = new PHPExcel_Worksheet_Drawing();
			$image->setName('Image');
			$image->setDescription('Image');
			$image->setPath($path);
			$image->setHeight(Config::inst()->get('ManifestReport', 'logo_height'));
			$image->setCoordinates('A1');
			$image->setWorksheet($sheet);
		}

		$this->extend('updateSheetHeaders', $sheet);
	}

	/**
	 * @param $sheetName
	 * @return PHPExcel_Worksheet
	 * @throws PHPExcel_Exception
	 */
	public function getExcelSheet($sheetName)
	{
		$excel = $this->getDocument();
		$sheet = $excel->getSheetByCodeName($this->getSheetCode($sheetName));
		if(!$sheet) {

			// check if first sheet is still not used.
			$firstSheet = $excel->getSheet(0);

			if($firstSheet->getCodeName() == 'Worksheet') {
				$firstSheet->setTitle($sheetName);
				$firstSheet->setCodeName($this->getSheetCode($sheetName));
				$sheet = $firstSheet;
			}
			else {
				$sheet = new PHPExcel_Worksheet(null, $sheetName);
				$sheet->setCodeName($this->getSheetCode($sheetName));
				$excel->addSheet($sheet);
			}

			$this->setSheetHeader($sheet);


		}

		return $sheet;
	}

	public function customManifestTitle()
	{
		return $this->getTitle();
	}


	public function displayArrayList(SS_List $list, $offset, PHPExcel_Worksheet $sheet, $titles = true)
	{
		$coordinates = PHPExcel_Cell::coordinateFromString($offset);

		$col = PHPExcel_Cell::columnIndexFromString($coordinates[0]);
		$row = $coordinates[1];

		$rowCounter = 0;
		foreach($list as $item){
			$map = $item->toMap();

			$colCounter = -1;
			$currentRow = $row + $rowCounter;



			if($rowCounter == 0 && $titles) {
				foreach($map as $name => $value) {
					$currentCol = $col + $colCounter;
					$sheet->setCellValueByColumnAndRow($currentCol, $currentRow, $name);
					$colCounter += 1;
				}
				$rowCounter += 1;
				$currentRow = $row + $rowCounter;
				$colCounter = -1;
			}

			foreach($map as $name => $value) {

				$currentCol = $col + $colCounter;
				$sheet->setCellValueByColumnAndRow($currentCol, $currentRow, $value);
				$colCounter += 1;
			}

			$rowCounter += 1;

		}


	}


	public function boldFont(PHPExcel_Worksheet $sheet, $col, $row)
	{
		$styles = array(
			'font'	=> array(
				'bold'			=> true,
				'color'			=> array('rgb'	=> '000000')
			)
		);

		$columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
		$coordinate = $columnLetter . $row;
		$sheet->getStyle($coordinate)->applyFromArray($styles);
	}

}