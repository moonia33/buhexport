# buhexport

PrestaShop 9 module to export invoices and credit slips to Pragma-compatible TXT (Windows-1257, TAB, CRLF).

- Encoding: Windows-1257
- Separator: TAB (\t)
- New lines: CRLF (\r\n)
- First row: column headers
- Configurable: file prefix, separate ID offsets (invoice/credit)

## Atsisiųsti

[![Download buhexport.zip](https://img.shields.io/badge/DOWNLOAD-buhexport.zip-brightgreen)](https://raw.githubusercontent.com/moonia33/buhexport/main/dist/buhexport.zip)

Tiesioginė nuoroda (startuoja atsisiuntimas):
[https://raw.githubusercontent.com/moonia33/buhexport/main/dist/buhexport.zip](https://raw.githubusercontent.com/moonia33/buhexport/main/dist/buhexport.zip)

## Įdiegimas

1. Back Office → Modules → Module Manager → Upload a module.
2. Įkelkite `buhexport.zip`.
3. Meniu: Orders → Buh. eksportas.
4. Pasirinkite datų intervalą, tipą, nustatykite „Failo prefiksą“ ir ID poslinkius (invoice/credit). Spauskite „Eksportuoti“.

## Nustatymai

- Failo prefiksas: `Configuration::get('BUHEXPORT_FILE_PREFIX')` (numatytasis `pragma_`).
- Invoice ID poslinkis: `Configuration::get('BUHEXPORT_ID_OFFSET_INVOICE')` (fallback į `BUHEXPORT_ID_OFFSET`, numatytasis `9665000`).
- Credit ID poslinkis: `Configuration::get('BUHEXPORT_ID_OFFSET_CREDIT')` (fallback į `BUHEXPORT_ID_OFFSET`, numatytasis `9665000`).

## Plėtra ir testavimas

- Modulio šaltinis: `modules/buhexport/`.
- Admin valdiklis: `controllers/admin/AdminBuhExportController.php`.
- Sukurti ZIP:

```bash
cd modules/buhexport
rm -f dist/buhexport.zip
mkdir -p dist
scripts/build-zip.sh
```

## Licencija

MIT (jei reikia, pakeiskite pagal projekto reikalavimus).
