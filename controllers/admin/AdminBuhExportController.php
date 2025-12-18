<?php
class AdminBuhExportController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        $this->content .= $this->renderForm();
        parent::initContent();
    }

    public function renderForm()
    {
        $this->fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Buhalterinis eksportas į Pragma', [], 'Modules.Buhexport.Admin'),
                    'icon' => 'icon-download',
                ],
                'input' => [
                    [
                        'type' => 'date',
                        'label' => $this->trans('Data nuo', [], 'Modules.Buhexport.Admin'),
                        'name' => 'date_from',
                        'required' => true,
                    ],
                    [
                        'type' => 'date',
                        'label' => $this->trans('Data iki', [], 'Modules.Buhexport.Admin'),
                        'name' => 'date_to',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Failo prefiksas', [], 'Modules.Buhexport.Admin'),
                        'name' => 'file_prefix',
                        'required' => false,
                        'desc' => $this->trans('Pvz.: pragma_ (bus naudojamas prieš orders_/credit_)', [], 'Modules.Buhexport.Admin'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Sąskaitų (invoice) ID poslinkis', [], 'Modules.Buhexport.Admin'),
                        'name' => 'id_offset_invoice',
                        'required' => true,
                        'desc' => $this->trans('Skaičius, pridedamas prie sąskaitos vidinio ID.', [], 'Modules.Buhexport.Admin'),
                        'suffix' => '',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Kreditinių (credit slip) ID poslinkis', [], 'Modules.Buhexport.Admin'),
                        'name' => 'id_offset_credit',
                        'required' => true,
                        'desc' => $this->trans('Skaičius, pridedamas prie kreditinės vidinio ID.', [], 'Modules.Buhexport.Admin'),
                        'suffix' => '',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Dokumento tipas', [], 'Modules.Buhexport.Admin'),
                        'name' => 'doc_type',
                        'options' => [
                            'query' => [
                                ['id' => 'invoice', 'name' => $this->trans('Sąskaitos (invoice)', [], 'Modules.Buhexport.Admin')],
                                ['id' => 'credit', 'name' => $this->trans('Kreditinės (credit slip)', [], 'Modules.Buhexport.Admin')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Eksportuoti', [], 'Modules.Buhexport.Admin'),
                    'name'  => 'submitExport',
                    'class' => 'btn btn-primary',
                    'icon'  => 'process-icon-download',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminBuhExport');
        $helper->currentIndex = Context::getContext()->link->getAdminLink('AdminBuhExport');
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = (int)Context::getContext()->language->id;
        // Fallback logika: jei naujų reikšmių nėra, naudoti seną BUHEXPORT_ID_OFFSET
        $fallbackOffset = Configuration::get('BUHEXPORT_ID_OFFSET', 9665000);
        $confInv = Configuration::get('BUHEXPORT_ID_OFFSET_INVOICE');
        $confCr = Configuration::get('BUHEXPORT_ID_OFFSET_CREDIT');
        $offsetInvoiceDefault = ($confInv === false || $confInv === null || $confInv === '') ? (int)$fallbackOffset : (int)$confInv;
        $offsetCreditDefault = ($confCr === false || $confCr === null || $confCr === '') ? (int)$fallbackOffset : (int)$confCr;

        $helper->fields_value = [
            'date_from' => Tools::getValue('date_from', date('Y-m-01', strtotime('first day of last month'))),
            'date_to'   => Tools::getValue('date_to', date('Y-m-t', strtotime('last day of last month'))),
            'file_prefix' => Tools::getValue('file_prefix', (string)Configuration::get('BUHEXPORT_FILE_PREFIX', 'pragma_')),
            'id_offset_invoice' => Tools::getValue('id_offset_invoice', $offsetInvoiceDefault),
            'id_offset_credit' => Tools::getValue('id_offset_credit', $offsetCreditDefault),
            'doc_type'  => Tools::getValue('doc_type', 'invoice'),
        ];

        return $helper->generateForm([$this->fields_form]);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitExport')) {
            $dateFrom = Tools::getValue('date_from');
            $dateTo = Tools::getValue('date_to');
            $docType = Tools::getValue('doc_type', 'invoice');
            $filePrefix = (string)Tools::getValue('file_prefix', (string)Configuration::get('BUHEXPORT_FILE_PREFIX', 'pragma_'));
            $fallbackOffset = Configuration::get('BUHEXPORT_ID_OFFSET', 9665000);
            $idOffsetInvoice = Tools::getValue('id_offset_invoice', Configuration::get('BUHEXPORT_ID_OFFSET_INVOICE'));
            if ($idOffsetInvoice === false || $idOffsetInvoice === null || $idOffsetInvoice === '') { $idOffsetInvoice = $fallbackOffset; }
            $idOffsetCredit = Tools::getValue('id_offset_credit', Configuration::get('BUHEXPORT_ID_OFFSET_CREDIT'));
            if ($idOffsetCredit === false || $idOffsetCredit === null || $idOffsetCredit === '') { $idOffsetCredit = $fallbackOffset; }
            $idOffsetInvoice = (int)$idOffsetInvoice;
            $idOffsetCredit = (int)$idOffsetCredit;
            if ($idOffsetInvoice < 0) { $idOffsetInvoice = 0; }
            if ($idOffsetCredit < 0) { $idOffsetCredit = 0; }
            Configuration::updateValue('BUHEXPORT_FILE_PREFIX', $filePrefix);
            // Išsaugome naujus atskirus poslinkius
            Configuration::updateValue('BUHEXPORT_ID_OFFSET_INVOICE', $idOffsetInvoice);
            Configuration::updateValue('BUHEXPORT_ID_OFFSET_CREDIT', $idOffsetCredit);

            if (!$dateFrom || !$dateTo) {
                $this->errors[] = $this->trans('Prašome nurodyti datų intervalą.', [], 'Modules.Buhexport.Admin');
                return parent::postProcess();
            }

            try {
                if ($docType === 'credit') {
                    $lines = $this->buildCreditExport($dateFrom, $dateTo);
                    $filename = $filePrefix . 'credit_' . date('Ymd_His') . '.txt';
                } else {
                    $lines = $this->buildInvoiceExport($dateFrom, $dateTo);
                    $filename = $filePrefix . 'orders_' . date('Ymd_His') . '.txt';
                }

                array_unshift($lines, $this->joinTsv($this->getHeaderFields()));
                $content = implode("\r\n", $lines) . "\r\n";
                $content1257 = @iconv('UTF-8', 'WINDOWS-1257//TRANSLIT', $content);
                if ($content1257 === false) {
                    $content1257 = mb_convert_encoding($content, 'WINDOWS-1257', 'UTF-8');
                }

                header('Content-Type: text/plain; charset=windows-1257');
                header('Content-Disposition: attachment; filename=' . $filename);
                header('Pragma: public');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Expires: 0');
                echo $content1257;
                exit;
            } catch (Exception $e) {
                $this->errors[] = $this->trans('Eksporto klaida: ', [], 'Modules.Buhexport.Admin') . $e->getMessage();
            }
        }

        return parent::postProcess();
    }

    protected function getHeaderFields()
    {
        return [
            'dokumento_id',
            'dokumento_tipas',
            'gr_dok_pozymis',
            'dok_data',
            'sask_data',
            'dok_nr',
            'pirkejo_kodas',
            'rez',
            'sandelis',
            'koresp',
            'val_kodas',
            'val_kursas',
            'pastaba',
            'dok_suma',
            'dok_sum_valiuta',
            'pvm_sum',
            'pvm_sum_valiuta',
            'prekiu_suma',
            'prekiu_suma_valiuta',
            'transp_islaid_sum',
            'muito_islaid_suma',
            'kitu_pridetiniu',
            'projekto_kodas',
            'fr_pozymis',
        ];
    }

    protected function buildInvoiceExport($dateFrom, $dateTo)
    {
        $df = pSQL($dateFrom . ' 00:00:00');
        $dt = pSQL($dateTo . ' 23:59:59');
        $prefix = _DB_PREFIX_;
        // Poslinkis sąskaitoms: naudoti naują raktą, jei nėra – seną
        $offsetRaw = Configuration::get('BUHEXPORT_ID_OFFSET_INVOICE');
        if ($offsetRaw === false || $offsetRaw === null || $offsetRaw === '') {
            $offsetRaw = Configuration::get('BUHEXPORT_ID_OFFSET', 9665000);
        }
        $offset = (int)$offsetRaw;
        $sql = "
            SELECT o.id_order AS id, o.invoice_number AS nr, o.invoice_date AS date,
                   o.total_paid_tax_excl AS bepvm, o.total_paid_tax_incl AS supvm
            FROM {$prefix}orders o
            WHERE o.invoice_date BETWEEN '{$df}' AND '{$dt}'
              AND o.invoice_number > 0
        ";
        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $lines = [];
        if (!$rows) {
            return $lines;
        }

        foreach ($rows as $row) {
            $id = (int)$row['id'] + $offset;
            $inv = null;
            $idInvoice = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT id_order_invoice FROM ' . bqSQL($prefix) . 'order_invoice WHERE id_order=' . (int)$row['id'] . ' AND number=' . (int)$row['nr']
            );
            if ($idInvoice) {
                $orderInvoice = new OrderInvoice($idInvoice);
                $inv = $orderInvoice->getInvoiceNumberFormatted((int)Context::getContext()->language->id, (int)Context::getContext()->shop->id);
            }
            if (!$inv) {
                $invoicePrefix = $this->getConfigPrefix('PS_INVOICE_PREFIX');
                $inv = ($invoicePrefix !== '' ? $invoicePrefix . '-' : '') . (int)$row['nr'];
            }
            $date = $row['date'] ?: date('Y-m-d');
            $date = date('Y-m-d', strtotime($date));
            $bepvm = (float)$row['bepvm'];
            $supvm = (float)$row['supvm'];
            $pvm = $supvm - $bepvm;

            $fields = [
                $id,
                '2',
                '1',
                $date,
                $date,
                $inv,
                '1234567890',
                '',
                'Sandėlis',
                'LT pard.prek.grynais',
                '',
                '0.00',
                '',
                $this->fmt($supvm),
                '0.00',
                $this->fmt($pvm),
                '0.00',
                $this->fmt($bepvm),
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '',
                '',
            ];
            $lines[] = $this->joinTsv($fields);
        }
        return $lines;
    }

    protected function buildCreditExport($dateFrom, $dateTo)
    {
        // Kreditinių eksportas: stengiamės naudoti order_slip sumines reikšmes (jei pasiekiamos šiame PS variante)
        $df = pSQL($dateFrom . ' 00:00:00');
        $dt = pSQL($dateTo . ' 23:59:59');
        $prefix = _DB_PREFIX_;
        // Poslinkis kreditinėms: naudoti naują raktą, jei nėra – seną
        $offsetRaw = Configuration::get('BUHEXPORT_ID_OFFSET_CREDIT');
        if ($offsetRaw === false || $offsetRaw === null || $offsetRaw === '') {
            $offsetRaw = Configuration::get('BUHEXPORT_ID_OFFSET', 9665000);
        }
        $offset = (int)$offsetRaw;

        // Bandoma apimti dažniausiai naudojamus stulpelius (total_products_tax_excl/incl). Jei jų nėra, sumažins reikšmes į 0.
        $sql = "
            SELECT os.id_order_slip AS id_slip, os.date_add AS date, os.id_order AS id_order,
                   IFNULL(os.total_products_tax_excl, 0) AS bepvm,
                   IFNULL(os.total_products_tax_incl, 0) AS supvm
            FROM {$prefix}order_slip os
            WHERE os.date_add BETWEEN '{$df}' AND '{$dt}'
        ";
        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $lines = [];
        if (!$rows) {
            return $lines;
        }

        foreach ($rows as $row) {
            $id = (int)$row['id_slip'] + $offset; // Vidaus ID pagal analogiją
            $shopId = (int)Context::getContext()->shop->id;
            $langId = (int)Context::getContext()->language->id;
            $creditPrefix = (string)Configuration::get('PS_CREDIT_SLIP_PREFIX', $langId, null, $shopId);
            $inv = ($creditPrefix !== '' ? $creditPrefix : '') . sprintf('%06d', (int)$row['id_slip']);
            $date = $row['date'] ?: date('Y-m-d');
            $date = date('Y-m-d', strtotime($date));
            $bepvm = (float)$row['bepvm'];
            $supvm = (float)$row['supvm'];
            $pvm = $supvm - $bepvm;

            // Kreditinėse dažnai sumos turėtų būti neigiamos (grąžinimas)
            $bepvm = -1 * abs($bepvm);
            $supvm = -1 * abs($supvm);
            $pvm = -1 * abs($pvm);

            $fields = [
                $id,
                '2',
                '1',
                $date,
                $date,
                $inv,
                '1234567890',
                '',
                'Sandėlis',
                'LT pard.prek.grynais',
                '',
                '0.00',
                '',
                $this->fmt($supvm),
                '0.00',
                $this->fmt($pvm),
                '0.00',
                $this->fmt($bepvm),
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '',
                '',
            ];
            $lines[] = $this->joinTsv($fields);
        }
        return $lines;
    }

    protected function joinTsv(array $fields)
    {
        $escaped = [];
        foreach ($fields as $f) {
            $s = (string)$f;
            $s = str_replace(["\r", "\n", "\t"], ' ', $s);
            $escaped[] = $s;
        }
        return implode("\t", $escaped);
    }

    protected function fmt($number)
    {
        return number_format((float)$number, 2, '.', '');
    }

    protected function getConfigPrefix($key)
    {
        $prefix = (string)Configuration::get($key);
        if ($prefix === '' || $prefix === null) {
            $shopId = (int)Context::getContext()->shop->id;
            $prefix = (string)Configuration::get($key, null, null, $shopId);
        }
        return trim((string)$prefix);
    }
}
