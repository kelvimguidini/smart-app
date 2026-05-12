<?php
$fileIn = '/var/www/html/resources/views/proposalPdf.blade.php';
$fileOut = '/var/www/html/resources/views/proposalPdfWithoutValues.blade.php';
$content = file_get_contents($fileIn);

// 1. Headers
$content = str_replace(
    "<th>Valor</th>\n                                <th>Taxas</th>\n                                <th>TTL com Taxa</th>\n                                <th>Total Geral</th>",
    "",
    $content
);
$content = str_replace(
    "<th>Valor</th>\r\n                                <th>Taxas</th>\r\n                                <th>TTL com Taxa</th>\r\n                                <th>Total Geral</th>",
    "",
    $content
);

// 2. Colspans in table headers
$content = str_replace('<th colspan="10" style="padding: 0.3rem; text-align: center;">HOSPEDAGEM</th>', '<th colspan="6" style="padding: 0.3rem; text-align: center;">HOSPEDAGEM</th>', $content);
$content = str_replace('<th colspan="10" style="padding: 0.3rem; text-align: center;">ALIMENTOS & BEBIDAS</th>', '<th colspan="6" style="padding: 0.3rem; text-align: center;">ALIMENTOS & BEBIDAS</th>', $content);
$content = str_replace('<th colspan="11" style="padding: 0.3rem; text-align: center;">SALÕES & EVENTOS</th>', '<th colspan="7" style="padding: 0.3rem; text-align: center;">SALÕES & EVENTOS</th>', $content);
$content = str_replace('<th colspan="11" style="padding: 0.3rem; text-align: center;">ADICIONAIS</th>', '<th colspan="7" style="padding: 0.3rem; text-align: center;">ADICIONAIS</th>', $content);
$content = str_replace('<th colspan="12" style="padding: 0.3rem; text-align: center;">TRANSPORTE TERRESTRE</th>', '<th colspan="8" style="padding: 0.3rem; text-align: center;">TRANSPORTE TERRESTRE</th>', $content);

// 3. Values in <tbody>
$events = ['hotelEvent', 'abEvent', 'hallEvent', 'addEvent', 'transportEvent'];
foreach ($events as $ev) {
    $content = preg_replace('/<td>\s*\{\{ formatCurrency\(unitSale\(\$item\), \$'.$ev.'->currency->symbol\) \}\}\s*<\/td>\s*<td>\s*\{\{ formatCurrency\(sumTaxesProvider\(\$'.$ev.', \$item\), \$'.$ev.'->currency->symbol\) \}\}\s*<\/td>\s*<td>\s*\{\{ formatCurrency\(unitSale\(\$item\) \+ sumTaxesProvider\(\$'.$ev.', \$item\), \$'.$ev.'->currency->symbol\) \}\}\s*<\/td>\s*<td>\s*\{\{ formatCurrency\(sumTotal\(unitSale\(\$item\), sumTaxesProvider\(\$'.$ev.', \$item\), \$item->count \* daysBetween[1]?\(\$item->in, \$item->out\)\), \$'.$ev.'->currency->symbol\) \}\}\s*<\/td>/s', '', $content);
}

// 4. Footers for the 5 tables
// Hotel
$content = preg_replace(
    '/<\?php \$hotelTaxa4BTS.*?<tfoot class="table-footer">.*?<tr style="background-color: #ffe0b1">.*?<td colspan="1"><b>Comentários:<\/b><\/td>.*?<td colspan="5">\{\{ \$hotelEvent->customer_observation \}\}<\/td>.*?<td><b>Serviço 4BTS.*?<\/td>.*?<td>.*?<\/td>.*?<td><b>Prazo<\/b><\/td>.*?<td>(.*?)<\/td>.*?<\/tr>.*?<\/tfoot>/s',
    '<tfoot class="table-footer"><tr style="background-color: #ffe0b1"><td colspan="1"><b>Comentários:</b></td><td colspan="3">{{ $hotelEvent->customer_observation }}</td><td><b>Prazo</b></td><td>$1</td></tr></tfoot>',
    $content
);
$content = preg_replace(
    '/<tfoot class="table-footer">\s*<\?php \$hotelTaxa4BTS.*?<tr style="background-color: #ffe0b1">\s*<td colspan="1"><b>Comentários:<\/b><\/td>\s*<td colspan="5">\{\{ \$hotelEvent->customer_observation \}\}<\/td>\s*<td><b>Serviço 4BTS.*?<\/td>\s*<td>.*?<\/td>\s*<td><b>Prazo<\/b><\/td>\s*<td>(.*?)<\/td>\s*<\/tr>\s*<\/tfoot>/s',
    '<tfoot class="table-footer"><tr style="background-color: #ffe0b1"><td colspan="1"><b>Comentários:</b></td><td colspan="3">{{ $hotelEvent->customer_observation }}</td><td><b>Prazo</b></td><td>$1</td></tr></tfoot>',
    $content
);


// AB
$content = preg_replace(
    '/<tfoot class="table-footer">\s*<\?php \$abTaxa4BTS.*?<tr style="background-color: #ffe0b1">\s*<td colspan="2"><b>Comentários:<\/b><\/td>\s*<td colspan="4">\{\{ \$abEvent->customer_observation \}\}<\/td>\s*<td><b>Serviço 4BTS.*?<\/td>\s*<td>.*?<\/td>\s*<td><b>Prazo<\/b><\/td>\s*<td>(.*?)<\/td>\s*<\/tr>\s*<\/tfoot>/s',
    '<tfoot class="table-footer"><tr style="background-color: #ffe0b1"><td colspan="1"><b>Comentários:</b></td><td colspan="3">{{ $abEvent->customer_observation }}</td><td><b>Prazo</b></td><td>$1</td></tr></tfoot>',
    $content
);

// Hall
$content = preg_replace(
    '/<tfoot class="table-footer">\s*<\?php \$hallTaxa4BTS.*?<tr style="background-color: #ffe0b1">\s*<td colspan="2"><b>Comentários:<\/b><\/td>\s*<td colspan="5">\{\{ \$hallEvent->customer_observation \}\}<\/td>\s*<td><b>Serviço 4BTS.*?<\/td>\s*<td>.*?<\/td>\s*<td><b>Prazo<\/b><\/td>\s*<td>(.*?)<\/td>\s*<\/tr>\s*<\/tfoot>/s',
    '<tfoot class="table-footer"><tr style="background-color: #ffe0b1"><td colspan="1"><b>Comentários:</b></td><td colspan="4">{{ $hallEvent->customer_observation }}</td><td><b>Prazo</b></td><td>$1</td></tr></tfoot>',
    $content
);

// Add
$content = preg_replace(
    '/<tfoot class="table-footer">\s*<\?php \$addTaxa4BTS.*?<tr style="background-color: #ffe0b1">\s*<td colspan="2"><b>Comentários:<\/b><\/td>\s*<td colspan="5">\{\{ \$addEvent->customer_observation \}\}<\/td>\s*<td><b>Serviço 4BTS.*?<\/td>\s*<td>.*?<\/td>\s*<td><b>Prazo<\/b><\/td>\s*<td>(.*?)<\/td>\s*<\/tr>\s*<\/tfoot>/s',
    '<tfoot class="table-footer"><tr style="background-color: #ffe0b1"><td colspan="1"><b>Comentários:</b></td><td colspan="4">{{ $addEvent->customer_observation }}</td><td><b>Prazo</b></td><td>$1</td></tr></tfoot>',
    $content
);

// Transport
$content = preg_replace(
    '/<tfoot class="table-footer">\s*<\?php \$transportTaxa4BTS.*?<tr style="background-color: #ffe0b1">\s*<td colspan="2"><b>Comentários:<\/b><\/td>\s*<td colspan="6">\{\{ \$transportEvent->customer_observation \}\}<\/td>\s*<td><b>Serviço 4BTS.*?<\/td>\s*<td>.*?<\/td>\s*<td><b>Prazo<\/b><\/td>\s*<td>(.*?)<\/td>\s*<\/tr>\s*<\/tfoot>/s',
    '<tfoot class="table-footer"><tr style="background-color: #ffe0b1"><td colspan="1"><b>Comentários:</b></td><td colspan="5">{{ $transportEvent->customer_observation }}</td><td><b>Prazo</b></td><td>$1</td></tr></tfoot>',
    $content
);

// 5. RESUMO DA PROPOSTA
// Update header
$content = preg_replace(
    '/<tr style="background-color: #e9540d; color: rgb\(250, 249, 249\);">\s*<th>Serviços<\/th>\s*<th colspan="2" style="text-align: left;">Totais de:<\/th>\s*<th>Preço médio<\/th>\s*<th>Total do Pedido<\/th>\s*<\/tr>/s',
    '<tr style="background-color: #e9540d; color: rgb(250, 249, 249);"><th>Serviços</th><th colspan="2" style="text-align: left;">Totais de:</th></tr>',
    $content
);
$content = str_replace('<th colspan="5" style="padding: 0.3rem; text-align:center;">', '<th colspan="3" style="padding: 0.3rem; text-align:center;">', $content);

// Remove value cells in summary
$content = preg_replace(
    '/<td>\{\{ formatCurrency\(\$sumValueRate \/ count\(\$hotelEvent->eventHotelsOpt\)\)\}\}<\/td>\s*<td>\{\{ formatCurrency\(\$sumTotalHotelValue\)\}\}<\/td>/s',
    '',
    $content
);
$content = preg_replace(
    '/<td>\{\{ formatCurrency\(\$sumABValueRate \/ count\(\$abEvent->eventAbOpts\)\)\}\}<\/td>\s*<td>\{\{formatCurrency\(\$sumTotalABValue\) \}\}\s*<\/td>/s',
    '',
    $content
);
$content = preg_replace(
    '/<td>\{\{ formatCurrency\(\$sumHallValueRate \/ count\(\$hallEvent->eventHallOpts\)\)\}\}<\/td>\s*<td>\{\{ formatCurrency\(\$sumTotalHallValue\) \}\}\s*<\/td>/s',
    '',
    $content
);
$content = preg_replace(
    '/<td>\{\{ formatCurrency\(\$sumAddValueRate \/ count\(\$addEvent->eventAddOpts\)\)\}\}<\/td>\s*<td>\{\{ formatCurrency\(\$sumTotalAddValue\) \}\}\s*<\/td>/s',
    '',
    $content
);
$content = preg_replace(
    '/<td>\{\{ formatCurrency\(\$sumTransportValueRate \/ count\(\$transportEvent->eventTransportOpts\)\)\}\}<\/td>\s*<td>\{\{ formatCurrency\(\$sumTotalTransportValue\)\}\}<\/td>/s',
    '',
    $content
);

// Remove footer of summary
$content = preg_replace('/<tfoot class="table-footer">\s*<tr style="background-color: #ebf8ff;">.*?<\/tfoot>/s', '', $content);


file_put_contents($fileOut, $content);
echo "Done";
