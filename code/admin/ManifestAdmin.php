<?php

/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 9/23/16
 * Time: 12:21 PM
 * To change this template use File | Settings | File Templates.
 */
class ManifestAdmin extends LeftAndMain implements PermissionProvider
{
	
	private static $url_segment = 'manifests';

	private static $url_rule = '/$ManifestClass/$Action';

	private static $menu_title = 'Manifests';

	private static $tree_class = 'ManifestReport';

	private static $url_handlers = array(
		'EditForm'				 => 'EditForm',
		'$ManifestClass/$Action' => 'handleAction'
	);

	protected $manifestClass;

	protected $manifestObject;


	public function init() {
		parent::init();

		//set the report we are currently viewing from the URL
		$this->manifestClass = (isset($this->urlParams['ManifestClass']) && $this->urlParams['ManifestClass'] !== 'index')
			? $this->urlParams['ManifestClass']
			: null;

		if($this->request->getVar('Manifest')) {
			$this->manifestClass = $this->request->getVar('Manifest');
		}

		$manifests = ManifestReport::get_manifests();
		$this->manifestObject = (isset($manifests[$this->manifestClass])) ? $manifests[$this->manifestClass] : null;
		Requirements::javascript('silverstripe-manifests/javascript/ManifestAdmin.js');
	}

	public function providePermissions() {
		return array(
			"CMS_ACCESS_ManifestAdmin" => array(
				'name' => _t('CMSMain.ACCESS', "Access to '{title}' section", array('title' => LeftAndMain::menu_title_for_class($this->class))),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access')
			)
		);
	}

	public function getManifests()
	{
		$output = new ArrayList();
		foreach(ManifestReport::get_manifests() as $manifest) {
			if($manifest->canView()) $output->push($manifest);
		}
		return $output;
	}




	public function getEditForm($id = null, $fields = null) {
		$manifest = $this->manifestObject;
		if($manifest) {
			$fields = $manifest->getCMSFields();
		} else {
			// List all reports
			$fields = new FieldList();
			$gridFieldConfig = GridFieldConfig::create()->addComponents(
				new GridFieldToolbarHeader(),
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldFooter()
			);
			$gridField = new GridField('Reports',false, $this->getManifests(), $gridFieldConfig);
			$columns = $gridField->getConfig()->getComponentByType('GridFieldDataColumns');
			$columns->setDisplayFields(array(
				'title' => _t('ReportAdmin.ReportTitle', 'Title'),
			));

			$columns->setFieldFormatting(array(
				'title' => '<a href=\"$Link\" class=\"cms-panel-link\">$value</a>'
			));

			$fields->push($gridField);
		}

		$actions = new FieldList();
		$form = new Form($this, "EditForm", $fields, $actions);
		$form->addExtraClass('cms-edit-form cms-panel-padded center ' . $this->BaseCSSClasses());
		$form->loadDataFrom($this->request->getVars());

		$this->extend('updateEditForm', $form);

		return $form;
	}



}