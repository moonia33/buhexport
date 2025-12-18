# buhexport

PrestaShop 9 module to export invoices and credit slips to Pragma-compatible TXT (Windows-1257, TAB, CRLF).

- Encoding: Windows-1257
- Separator: TAB (\t)
- New lines: CRLF (\r\n)
- First row: column headers
- Configurable: file prefix, ID offset

## Atsisiųsti

[![Download buhexport.zip](https://img.shields.io/badge/DOWNLOAD-buhexport.zip-brightgreen)](dist/buhexport.zip)

Arba tiesioginė nuoroda: [Download buhexport.zip (latest build)](dist/buhexport.zip)

## Įdiegimas

1. Back Office → Modules → Module Manager → Upload a module.
2. Įkelkite `buhexport.zip`.
3. Meniu: Orders → Buh. eksportas.
4. Pasirinkite datų intervalą, tipą, nustatykite „Failo prefiksą“ ir „ID poslinkį“. Spauskite „Eksportuoti“.

## Nustatymai

- Failo prefiksas: `Configuration::get('BUHEXPORT_FILE_PREFIX')` (numatytasis `pragma_`).
- ID poslinkis: `Configuration::get('BUHEXPORT_ID_OFFSET')` (numatytasis `9665000`).

## Plėtra ir testavimas

- Modulio šaltinis: `modules/buhexport/`.
- Admin valdiklis: `controllers/admin/AdminBuhExportController.php`.
- Sukurti ZIP:

```bash
cd modules/buhexport
rm -f dist/buhexport.zip
mkdir -p dist
zip -r dist/buhexport.zip . \
    -x "dist/*" "cache/*" "vendor/*" "*.git*"
```

## Licencija

MIT (jei reikia, pakeiskite pagal projekto reikalavimus).
