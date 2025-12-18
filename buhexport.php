<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Buhexport extends Module
{
    public function __construct()
    {
        $this->name = 'buhexport';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Custom';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Buhalterinis eksportas');
        $this->description = $this->l('Eksportuoja užsakymų ir kreditinių duomenis į Pragma (TSV, Windows-1257).');
    }

    public function install()
    {
        return parent::install()
            && $this->installTab(
                'AdminBuhExport',
                $this->l('Buh. eksportas'),
                'AdminParentOrders'
            )
            && Configuration::updateValue('BUHEXPORT_ID_OFFSET', 9665000)
            && Configuration::updateValue('BUHEXPORT_ID_OFFSET_INVOICE', 9665000)
            && Configuration::updateValue('BUHEXPORT_ID_OFFSET_CREDIT', 9665000)
            && Configuration::updateValue('BUHEXPORT_FILE_PREFIX', 'pragma_');
    }

    public function uninstall()
    {
        return $this->uninstallTab('AdminBuhExport')
            && Configuration::deleteByName('BUHEXPORT_FILE_PREFIX')
            && Configuration::deleteByName('BUHEXPORT_ID_OFFSET')
            && Configuration::deleteByName('BUHEXPORT_ID_OFFSET_INVOICE')
            && Configuration::deleteByName('BUHEXPORT_ID_OFFSET_CREDIT')
            && parent::uninstall();
    }

    protected function installTab($className, $tabName, $parentClassName)
    {
        $id_parent = (int)Tab::getIdFromClassName($parentClassName);
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = [];
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        $tab->id_parent = $id_parent ?: 0;
        $tab->module = $this->name;
        return (bool)$tab->add();
    }

    protected function uninstallTab($className)
    {
        $id_tab = (int)Tab::getIdFromClassName($className);
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return (bool)$tab->delete();
        }
        return true;
    }

    public function getContent()
    {
        $link = Context::getContext()->link;
        $token = Tools::getAdminTokenLite('AdminBuhExport');
        $url = $link->getAdminLink('AdminBuhExport');
        $html = '<div class="panel"><div class="panel-heading">' . $this->displayName . '</div>';
        $html .= '<p>' . $this->l('Eksporto sąsaja prieinama per užsakymų meniu punktą:') . ' ';
        $html .= '<a class="btn btn-default" href="' . htmlspecialchars($url . '&token=' . $token) . '">';
        $html .= '<i class="icon-download"></i> ' . $this->l('Eiti į Buh. eksportą') . '</a></p></div>';
        return $html;
    }
}
