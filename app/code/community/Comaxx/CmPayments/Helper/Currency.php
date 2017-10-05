<?php

/**
 * Generic currency helper class
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Helper_Currency extends Mage_Core_Helper_Abstract
{

    private static $_currencies = array(
        'AFN' => array(
            'entity'       => 'AFGANISTAN',
            'currency'     => 'Afghani',
            'numeric_code' => '971',
            'minor_unit'   => 2,
        ),
        'EUR' => array(
            'entity'       => 'EUROPE',
            'currency'     => 'Euro',
            'numeric_code' => '978',
            'minor_unit'   => 2,
        ),
        'ALL' => array(
            'entity'       => 'ALBANIA',
            'currency'     => 'Lek',
            'numeric_code' => '008',
            'minor_unit'   => 2,
        ),
        'DZD' => array(
            'entity'       => 'ALGERIA',
            'currency'     => 'Algerian Dinar',
            'numeric_code' => '012',
            'minor_unit'   => 2,
        ),
        'USD' => array(
            'entity'       => 'AMERICAN SAMOA',
            'currency'     => 'US Dollar',
            'numeric_code' => '840',
            'minor_unit'   => 2,
        ),
        'AOA' => array(
            'entity'       => 'ANGOLA',
            'currency'     => 'Kwanza',
            'numeric_code' => '973',
            'minor_unit'   => 2,
        ),
        'XCD' => array(
            'entity'       => 'ANGUILLA',
            'currency'     => 'East Caribbean Dollar',
            'numeric_code' => '951',
            'minor_unit'   => 2,
        ),
        'ARS' => array(
            'entity'       => 'ARGENTINA',
            'currency'     => 'Argentine Peso',
            'numeric_code' => '032',
            'minor_unit'   => 2,
        ),
        'AMD' => array(
            'entity'       => 'ARMENIA',
            'currency'     => 'Armenian Dram',
            'numeric_code' => '051',
            'minor_unit'   => 2,
        ),
        'AWG' => array(
            'entity'       => 'ARUBA',
            'currency'     => 'Aruban Guilder',
            'numeric_code' => '533',
            'minor_unit'   => 2,
        ),
        'AUD' => array(
            'entity'       => 'AUSTRALIA',
            'currency'     => 'Australian Dollar',
            'numeric_code' => '036',
            'minor_unit'   => 2,
        ),
        'AZN' => array(
            'entity'       => 'AZERBAIJAN',
            'currency'     => 'Azerbaijanian Manat',
            'numeric_code' => '944',
            'minor_unit'   => 2,
        ),
        'BSD' => array(
            'entity'       => 'BAHAMAS',
            'currency'     => 'Bahamian Dollar',
            'numeric_code' => '044',
            'minor_unit'   => 2,
        ),
        'BHD' => array(
            'entity'       => 'BAHRAIN',
            'currency'     => 'Bahraini Dinar',
            'numeric_code' => '048',
            'minor_unit'   => 3,
        ),
        'BDT' => array(
            'entity'       => 'BANGLADESH',
            'currency'     => 'Taka',
            'numeric_code' => '050',
            'minor_unit'   => 2,
        ),
        'BBD' => array(
            'entity'       => 'BARBADOS',
            'currency'     => 'Barbados Dollar',
            'numeric_code' => '052',
            'minor_unit'   => 2,
        ),
        'BYR' => array(
            'entity'       => 'BELARUS',
            'currency'     => 'Belarussian Ruble',
            'numeric_code' => '974',
            'minor_unit'   => 0,
        ),
        'BZD' => array(
            'entity'       => 'BELIZE',
            'currency'     => 'Belize Dollar',
            'numeric_code' => '084',
            'minor_unit'   => 2,
        ),
        'XOF' => array(
            'entity'       => 'BENIN',
            'currency'     => 'CFA Franc BCEAO ',
            'numeric_code' => '952',
            'minor_unit'   => 0,
        ),
        'BMD' => array(
            'entity'       => 'BERMUDA',
            'currency'     => 'Bermudian Dollar',
            'numeric_code' => '060',
            'minor_unit'   => 2,
        ),
        'INR' => array(
            'entity'       => 'BHUTAN',
            'currency'     => 'Indian Rupee',
            'numeric_code' => '356',
            'minor_unit'   => 2,
        ),
        'BTN' => array(
            'entity'       => 'BHUTAN',
            'currency'     => 'Ngultrum ',
            'numeric_code' => '064',
            'minor_unit'   => 2,
        ),
        'BOB' => array(
            'entity'       => 'BOLIVIA, PLURINATIONAL STATE OF',
            'currency'     => 'Boliviano',
            'numeric_code' => '068',
            'minor_unit'   => 2,
        ),
        'BOV' => array(
            'entity'       => 'BOLIVIA, PLURINATIONAL STATE OF',
            'currency'     => 'Mvdol ',
            'numeric_code' => '984',
            'minor_unit'   => 2,
        ),
        'BAM' => array(
            'entity'       => 'BOSNIA &amp; HERZEGOVINA',
            'currency'     => 'Convertible Mark',
            'numeric_code' => '977',
            'minor_unit'   => 2,
        ),
        'BWP' => array(
            'entity'       => 'BOTSWANA',
            'currency'     => 'Pula',
            'numeric_code' => '072',
            'minor_unit'   => 2,
        ),
        'NOK' => array(
            'entity'       => 'SVALBARD AND JAN MAYEN',
            'currency'     => 'Norwegian Krone',
            'numeric_code' => '578',
            'minor_unit'   => 2,
        ),
        'BRL' => array(
            'entity'       => 'BRAZIL',
            'currency'     => 'Brazilian Real',
            'numeric_code' => '986',
            'minor_unit'   => 2,
        ),
        'BND' => array(
            'entity'       => 'BRUNEI DARUSSALAM',
            'currency'     => 'Brunei Dollar',
            'numeric_code' => '096',
            'minor_unit'   => 2,
        ),
        'BGN' => array(
            'entity'       => 'BULGARIA',
            'currency'     => 'Bulgarian Lev',
            'numeric_code' => '975',
            'minor_unit'   => 2,
        ),
        'BIF' => array(
            'entity'       => 'BURUNDI',
            'currency'     => 'Burundi Franc',
            'numeric_code' => '108',
            'minor_unit'   => 0,
        ),
        'KHR' => array(
            'entity'       => 'CAMBODIA',
            'currency'     => 'Riel',
            'numeric_code' => '116',
            'minor_unit'   => 2,
        ),
        'XAF' => array(
            'entity'       => 'GABON',
            'currency'     => 'CFA Franc BEAC ',
            'numeric_code' => '950',
            'minor_unit'   => 0,
        ),
        'CAD' => array(
            'entity'       => 'CANADA',
            'currency'     => 'Canadian Dollar',
            'numeric_code' => '124',
            'minor_unit'   => 2,
        ),
        'CVE' => array(
            'entity'       => 'CAPE VERDE',
            'currency'     => 'Cape Verde Escudo',
            'numeric_code' => '132',
            'minor_unit'   => 2,
        ),
        'KYD' => array(
            'entity'       => 'CAYMAN ISLANDS',
            'currency'     => 'Cayman Islands Dollar',
            'numeric_code' => '136',
            'minor_unit'   => 2,
        ),
        'CLP' => array(
            'entity'       => 'CHILE',
            'currency'     => 'Chilean Peso',
            'numeric_code' => '152',
            'minor_unit'   => 0,
        ),
        'CLF' => array(
            'entity'       => 'CHILE',
            'currency'     => 'Unidades de fomento ',
            'numeric_code' => '990',
            'minor_unit'   => 0,
        ),
        'CNY' => array(
            'entity'       => 'CHINA',
            'currency'     => 'Yuan Renminbi',
            'numeric_code' => '156',
            'minor_unit'   => 2,
        ),
        'COP' => array(
            'entity'       => 'COLOMBIA',
            'currency'     => 'Colombian Peso',
            'numeric_code' => '170',
            'minor_unit'   => 2,
        ),
        'COU' => array(
            'entity'       => 'COLOMBIA',
            'currency'     => 'Unidad de Valor Real ',
            'numeric_code' => '970',
            'minor_unit'   => 2,
        ),
        'KMF' => array(
            'entity'       => 'COMOROS',
            'currency'     => 'Comoro Franc',
            'numeric_code' => '174',
            'minor_unit'   => 0,
        ),
        'CDF' => array(
            'entity'       => 'CONGO, THE DEMOCRATIC REPUBLIC OF',
            'currency'     => 'Congolese Franc ',
            'numeric_code' => '976',
            'minor_unit'   => 2,
        ),
        'NZD' => array(
            'entity'       => 'TOKELAU',
            'currency'     => 'New Zealand Dollar',
            'numeric_code' => '554',
            'minor_unit'   => 2,
        ),
        'CRC' => array(
            'entity'       => 'COSTA RICA',
            'currency'     => 'Costa Rican Colon',
            'numeric_code' => '188',
            'minor_unit'   => 2,
        ),
        'HRK' => array(
            'entity'       => 'CROATIA',
            'currency'     => 'Croatian Kuna',
            'numeric_code' => '191',
            'minor_unit'   => 2,
        ),
        'CUP' => array(
            'entity'       => 'CUBA',
            'currency'     => 'Cuban Peso',
            'numeric_code' => '192',
            'minor_unit'   => 2,
        ),
        'CUC' => array(
            'entity'       => 'CUBA',
            'currency'     => 'Peso Convertible ',
            'numeric_code' => '931',
            'minor_unit'   => 2,
        ),
        'ANG' => array(
            'entity'       => 'SINT MAARTEN (DUTCH PART)',
            'currency'     => 'Netherlands Antillean Guilder',
            'numeric_code' => '532',
            'minor_unit'   => 2,
        ),
        'CZK' => array(
            'entity'       => 'CZECH REPUBLIC',
            'currency'     => 'Czech Koruna',
            'numeric_code' => '203',
            'minor_unit'   => 2,
        ),
        'DKK' => array(
            'entity'       => 'GREENLAND',
            'currency'     => 'Danish Krone',
            'numeric_code' => '208',
            'minor_unit'   => 2,
        ),
        'DJF' => array(
            'entity'       => 'DJIBOUTI',
            'currency'     => 'Djibouti Franc',
            'numeric_code' => '262',
            'minor_unit'   => 0,
        ),
        'DOP' => array(
            'entity'       => 'DOMINICAN REPUBLIC',
            'currency'     => 'Dominican Peso',
            'numeric_code' => '214',
            'minor_unit'   => 2,
        ),
        'EGP' => array(
            'entity'       => 'EGYPT',
            'currency'     => 'Egyptian Pound',
            'numeric_code' => '818',
            'minor_unit'   => 2,
        ),
        'SVC' => array(
            'entity'       => 'EL SALVADOR',
            'currency'     => 'El Salvador Colon',
            'numeric_code' => '222',
            'minor_unit'   => 2,
        ),
        'ERN' => array(
            'entity'       => 'ERITREA',
            'currency'     => 'Nakfa',
            'numeric_code' => '232',
            'minor_unit'   => 2,
        ),
        'ETB' => array(
            'entity'       => 'ETHIOPIA',
            'currency'     => 'Ethiopian Birr',
            'numeric_code' => '230',
            'minor_unit'   => 2,
        ),
        'FKP' => array(
            'entity'       => 'FALKLAND ISLANDS (MALVINAS)',
            'currency'     => 'Falkland Islands Pound',
            'numeric_code' => '238',
            'minor_unit'   => 2,
        ),
        'FJD' => array(
            'entity'       => 'FIJI',
            'currency'     => 'Fiji Dollar',
            'numeric_code' => '242',
            'minor_unit'   => 2,
        ),
        'XPF' => array(
            'entity'       => 'WALLIS AND FUTUNA',
            'currency'     => 'CFP Franc',
            'numeric_code' => '953',
            'minor_unit'   => 0,
        ),
        'GMD' => array(
            'entity'       => 'GAMBIA',
            'currency'     => 'Dalasi',
            'numeric_code' => '270',
            'minor_unit'   => 2,
        ),
        'GEL' => array(
            'entity'       => 'GEORGIA',
            'currency'     => 'Lari',
            'numeric_code' => '981',
            'minor_unit'   => 2,
        ),
        'GHS' => array(
            'entity'       => 'GHANA',
            'currency'     => 'Cedi',
            'numeric_code' => '936',
            'minor_unit'   => 2,
        ),
        'GIP' => array(
            'entity'       => 'GIBRALTAR',
            'currency'     => 'Gibraltar Pound',
            'numeric_code' => '292',
            'minor_unit'   => 2,
        ),
        'GTQ' => array(
            'entity'       => 'GUATEMALA',
            'currency'     => 'Quetzal',
            'numeric_code' => '320',
            'minor_unit'   => 2,
        ),
        'GBP' => array(
            'entity'       => 'UNITED KINGDOM',
            'currency'     => 'Pound Sterling',
            'numeric_code' => '826',
            'minor_unit'   => 2,
        ),
        'GNF' => array(
            'entity'       => 'GUINEA',
            'currency'     => 'Guinea Franc',
            'numeric_code' => '324',
            'minor_unit'   => 0,
        ),
        'GYD' => array(
            'entity'       => 'GUYANA',
            'currency'     => 'Guyana Dollar',
            'numeric_code' => '328',
            'minor_unit'   => 2,
        ),
        'HTG' => array(
            'entity'       => 'HAITI',
            'currency'     => 'Gourde',
            'numeric_code' => '332',
            'minor_unit'   => 2,
        ),
        'HNL' => array(
            'entity'       => 'HONDURAS',
            'currency'     => 'Lempira',
            'numeric_code' => '340',
            'minor_unit'   => 2,
        ),
        'HKD' => array(
            'entity'       => 'HONG KONG',
            'currency'     => 'Hong Kong Dollar',
            'numeric_code' => '344',
            'minor_unit'   => 2,
        ),
        'HUF' => array(
            'entity'       => 'HUNGARY',
            'currency'     => 'Forint',
            'numeric_code' => '348',
            'minor_unit'   => 2,
        ),
        'ISK' => array(
            'entity'       => 'ICELAND',
            'currency'     => 'Iceland Krona',
            'numeric_code' => '352',
            'minor_unit'   => 0,
        ),
        'IDR' => array(
            'entity'       => 'INDONESIA',
            'currency'     => 'Rupiah',
            'numeric_code' => '360',
            'minor_unit'   => 2,
        ),
        'XDR' => array(
            'entity'       => 'INTERNATIONAL MONETARY FUND (IMF) ',
            'currency'     => 'SDR (Special Drawing Right)',
            'numeric_code' => '960',
            'minor_unit'   => null,
        ),
        'IRR' => array(
            'entity'       => 'IRAN, ISLAMIC REPUBLIC OF',
            'currency'     => 'Iranian Rial',
            'numeric_code' => '364',
            'minor_unit'   => 2,
        ),
        'IQD' => array(
            'entity'       => 'IRAQ',
            'currency'     => 'Iraqi Dinar',
            'numeric_code' => '368',
            'minor_unit'   => 3,
        ),
        'ILS' => array(
            'entity'       => 'ISRAEL',
            'currency'     => 'New Israeli Sheqel',
            'numeric_code' => '376',
            'minor_unit'   => 2,
        ),
        'JMD' => array(
            'entity'       => 'JAMAICA',
            'currency'     => 'Jamaican Dollar',
            'numeric_code' => '388',
            'minor_unit'   => 2,
        ),
        'JPY' => array(
            'entity'       => 'JAPAN',
            'currency'     => 'Yen',
            'numeric_code' => '392',
            'minor_unit'   => 0,
        ),
        'JOD' => array(
            'entity'       => 'JORDAN',
            'currency'     => 'Jordanian Dinar',
            'numeric_code' => '400',
            'minor_unit'   => 3,
        ),
        'KZT' => array(
            'entity'       => 'KAZAKHSTAN',
            'currency'     => 'Tenge',
            'numeric_code' => '398',
            'minor_unit'   => 2,
        ),
        'KES' => array(
            'entity'       => 'KENYA',
            'currency'     => 'Kenyan Shilling',
            'numeric_code' => '404',
            'minor_unit'   => 2,
        ),
        'KPW' => array(
            'entity'       => 'KOREA, DEMOCRATIC PEOPLE?S REPUBLIC OF',
            'currency'     => 'North Korean Won',
            'numeric_code' => '408',
            'minor_unit'   => 2,
        ),
        'KRW' => array(
            'entity'       => 'KOREA, REPUBLIC OF',
            'currency'     => 'Won',
            'numeric_code' => '410',
            'minor_unit'   => 0,
        ),
        'KWD' => array(
            'entity'       => 'KUWAIT',
            'currency'     => 'Kuwaiti Dinar',
            'numeric_code' => '414',
            'minor_unit'   => 3,
        ),
        'KGS' => array(
            'entity'       => 'KYRGYZSTAN',
            'currency'     => 'Som',
            'numeric_code' => '417',
            'minor_unit'   => 2,
        ),
        'LAK' => array(
            'entity'       => 'LAO PEOPLE?S DEMOCRATIC REPUBLIC',
            'currency'     => 'Kip',
            'numeric_code' => '418',
            'minor_unit'   => 2,
        ),
        'LVL' => array(
            'entity'       => 'LATVIA',
            'currency'     => 'Latvian Lats',
            'numeric_code' => '428',
            'minor_unit'   => 2,
        ),
        'LBP' => array(
            'entity'       => 'LEBANON',
            'currency'     => 'Lebanese Pound',
            'numeric_code' => '422',
            'minor_unit'   => 2,
        ),
        'LSL' => array(
            'entity'       => 'LESOTHO',
            'currency'     => 'Loti',
            'numeric_code' => '426',
            'minor_unit'   => 2,
        ),
        'ZAR' => array(
            'entity'       => 'SOUTH AFRICA',
            'currency'     => 'Rand',
            'numeric_code' => '710',
            'minor_unit'   => 2,
        ),
        'LRD' => array(
            'entity'       => 'LIBERIA',
            'currency'     => 'Liberian Dollar',
            'numeric_code' => '430',
            'minor_unit'   => 2,
        ),
        'LYD' => array(
            'entity'       => 'LIBYAN ARAB JAMAHIRIYA',
            'currency'     => 'Libyan Dinar',
            'numeric_code' => '434',
            'minor_unit'   => 3,
        ),
        'CHF' => array(
            'entity'       => 'SWITZERLAND',
            'currency'     => 'Swiss Franc',
            'numeric_code' => '756',
            'minor_unit'   => 2,
        ),
        'LTL' => array(
            'entity'       => 'LITHUANIA',
            'currency'     => 'Lithuanian Litas',
            'numeric_code' => '440',
            'minor_unit'   => 2,
        ),
        'MOP' => array(
            'entity'       => 'MACAO',
            'currency'     => 'Pataca',
            'numeric_code' => '446',
            'minor_unit'   => 2,
        ),
        'MKD' => array(
            'entity'       => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
            'currency'     => 'Denar',
            'numeric_code' => '807',
            'minor_unit'   => 2,
        ),
        'MGA' => array(
            'entity'       => 'MADAGASCAR',
            'currency'     => 'Malagasy Ariary',
            'numeric_code' => '969',
            'minor_unit'   => 2,
        ),
        'MWK' => array(
            'entity'       => 'MALAWI',
            'currency'     => 'Kwacha',
            'numeric_code' => '454',
            'minor_unit'   => 2,
        ),
        'MYR' => array(
            'entity'       => 'MALAYSIA',
            'currency'     => 'Malaysian Ringgit',
            'numeric_code' => '458',
            'minor_unit'   => 2,
        ),
        'MVR' => array(
            'entity'       => 'MALDIVES',
            'currency'     => 'Rufiyaa',
            'numeric_code' => '462',
            'minor_unit'   => 2,
        ),
        'MRO' => array(
            'entity'       => 'MAURITANIA',
            'currency'     => 'Ouguiya',
            'numeric_code' => '478',
            'minor_unit'   => 2,
        ),
        'MUR' => array(
            'entity'       => 'MAURITIUS',
            'currency'     => 'Mauritius Rupee',
            'numeric_code' => '480',
            'minor_unit'   => 2,
        ),
        'XUA' => array(
            'entity'       => 'MEMBER COUNTRIES OF THE AFRICAN DEVELOPMENT BANK GROUP',
            'currency'     => 'ADB Unit of Account',
            'numeric_code' => '965',
            'minor_unit'   => null,
        ),
        'MXN' => array(
            'entity'       => 'MEXICO',
            'currency'     => 'Mexican Peso',
            'numeric_code' => '484',
            'minor_unit'   => 2,
        ),
        'MXV' => array(
            'entity'       => 'MEXICO',
            'currency'     => 'Mexican Unidad de Inversion (UDI) ',
            'numeric_code' => '979',
            'minor_unit'   => 2,
        ),
        'MDL' => array(
            'entity'       => 'MOLDOVA, REPUBLIC OF',
            'currency'     => 'Moldovan Leu',
            'numeric_code' => '498',
            'minor_unit'   => 2,
        ),
        'MNT' => array(
            'entity'       => 'MONGOLIA',
            'currency'     => 'Tugrik',
            'numeric_code' => '496',
            'minor_unit'   => 2,
        ),
        'MAD' => array(
            'entity'       => 'WESTERN SAHARA',
            'currency'     => 'Moroccan Dirham',
            'numeric_code' => '504',
            'minor_unit'   => 2,
        ),
        'MZN' => array(
            'entity'       => 'MOZAMBIQUE',
            'currency'     => 'Metical',
            'numeric_code' => '943',
            'minor_unit'   => 2,
        ),
        'MMK' => array(
            'entity'       => 'MYANMAR',
            'currency'     => 'Kyat',
            'numeric_code' => '104',
            'minor_unit'   => 2,
        ),
        'NAD' => array(
            'entity'       => 'NAMIBIA',
            'currency'     => 'Namibia Dollar',
            'numeric_code' => '516',
            'minor_unit'   => 2,
        ),
        'NPR' => array(
            'entity'       => 'NEPAL',
            'currency'     => 'Nepalese Rupee',
            'numeric_code' => '524',
            'minor_unit'   => 2,
        ),
        'NIO' => array(
            'entity'       => 'NICARAGUA',
            'currency'     => 'Cordoba Oro',
            'numeric_code' => '558',
            'minor_unit'   => 2,
        ),
        'NGN' => array(
            'entity'       => 'NIGERIA',
            'currency'     => 'Naira',
            'numeric_code' => '566',
            'minor_unit'   => 2,
        ),
        'OMR' => array(
            'entity'       => 'OMAN',
            'currency'     => 'Rial Omani',
            'numeric_code' => '512',
            'minor_unit'   => 3,
        ),
        'PKR' => array(
            'entity'       => 'PAKISTAN',
            'currency'     => 'Pakistan Rupee',
            'numeric_code' => '586',
            'minor_unit'   => 2,
        ),
        'PAB' => array(
            'entity'       => 'PANAMA',
            'currency'     => 'Balboa',
            'numeric_code' => '590',
            'minor_unit'   => 2,
        ),
        'PGK' => array(
            'entity'       => 'PAPUA NEW GUINEA',
            'currency'     => 'Kina',
            'numeric_code' => '598',
            'minor_unit'   => 2,
        ),
        'PYG' => array(
            'entity'       => 'PARAGUAY',
            'currency'     => 'Guarani',
            'numeric_code' => '600',
            'minor_unit'   => 0,
        ),
        'PEN' => array(
            'entity'       => 'PERU',
            'currency'     => 'Nuevo Sol',
            'numeric_code' => '604',
            'minor_unit'   => 2,
        ),
        'PHP' => array(
            'entity'       => 'PHILIPPINES',
            'currency'     => 'Philippine Peso',
            'numeric_code' => '608',
            'minor_unit'   => 2,
        ),
        'PLN' => array(
            'entity'       => 'POLAND',
            'currency'     => 'Zloty',
            'numeric_code' => '985',
            'minor_unit'   => 2,
        ),
        'QAR' => array(
            'entity'       => 'QATAR',
            'currency'     => 'Qatari Rial',
            'numeric_code' => '634',
            'minor_unit'   => 2,
        ),
        'RON' => array(
            'entity'       => 'ROMANIA',
            'currency'     => 'Leu ',
            'numeric_code' => '946',
            'minor_unit'   => 2,
        ),
        'RUB' => array(
            'entity'       => 'RUSSIAN FEDERATION',
            'currency'     => 'Russian Ruble',
            'numeric_code' => '643',
            'minor_unit'   => 2,
        ),
        'RWF' => array(
            'entity'       => 'RWANDA',
            'currency'     => 'Rwanda Franc',
            'numeric_code' => '646',
            'minor_unit'   => 0,
        ),
        'SHP' => array(
            'entity'       => 'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA',
            'currency'     => 'Saint Helena Pound',
            'numeric_code' => '654',
            'minor_unit'   => 2,
        ),
        'WST' => array(
            'entity'       => 'SAMOA',
            'currency'     => 'Tala',
            'numeric_code' => '882',
            'minor_unit'   => 2,
        ),
        'STD' => array(
            'entity'       => 'S?O TOME AND PRINCIPE',
            'currency'     => 'Dobra',
            'numeric_code' => '678',
            'minor_unit'   => 2,
        ),
        'SAR' => array(
            'entity'       => 'SAUDI ARABIA',
            'currency'     => 'Saudi Riyal',
            'numeric_code' => '682',
            'minor_unit'   => 2,
        ),
        'RSD' => array(
            'entity'       => 'SERBIA ',
            'currency'     => 'Serbian Dinar',
            'numeric_code' => '941',
            'minor_unit'   => 2,
        ),
        'SCR' => array(
            'entity'       => 'SEYCHELLES',
            'currency'     => 'Seychelles Rupee',
            'numeric_code' => '690',
            'minor_unit'   => 2,
        ),
        'SLL' => array(
            'entity'       => 'SIERRA LEONE',
            'currency'     => 'Leone',
            'numeric_code' => '694',
            'minor_unit'   => 2,
        ),
        'SGD' => array(
            'entity'       => 'SINGAPORE',
            'currency'     => 'Singapore Dollar',
            'numeric_code' => '702',
            'minor_unit'   => 2,
        ),
        'XSU' => array(
            'entity'       => 'SISTEMA UNITARIO DE COMPENSACION REGIONAL DE PAGOS "SUCRE" ',
            'currency'     => 'Sucre',
            'numeric_code' => '994',
            'minor_unit'   => 0,
        ),
        'SBD' => array(
            'entity'       => 'SOLOMON ISLANDS',
            'currency'     => 'Solomon Islands Dollar',
            'numeric_code' => '090',
            'minor_unit'   => 2,
        ),
        'SOS' => array(
            'entity'       => 'SOMALIA',
            'currency'     => 'Somali Shilling',
            'numeric_code' => '706',
            'minor_unit'   => 2,
        ),
        'LKR' => array(
            'entity'       => 'SRI LANKA',
            'currency'     => 'Sri Lanka Rupee',
            'numeric_code' => '144',
            'minor_unit'   => 2,
        ),
        'SDG' => array(
            'entity'       => 'SUDAN',
            'currency'     => 'Sudanese Pound ',
            'numeric_code' => '938',
            'minor_unit'   => 2,
        ),
        'SRD' => array(
            'entity'       => 'SURINAME',
            'currency'     => 'Surinam Dollar',
            'numeric_code' => '968',
            'minor_unit'   => 2,
        ),
        'SZL' => array(
            'entity'       => 'SWAZILAND',
            'currency'     => 'Lilangeni',
            'numeric_code' => '748',
            'minor_unit'   => 2,
        ),
        'SEK' => array(
            'entity'       => 'SWEDEN',
            'currency'     => 'Swedish Krona',
            'numeric_code' => '752',
            'minor_unit'   => 2,
        ),
        'CHE' => array(
            'entity'       => 'SWITZERLAND',
            'currency'     => 'WIR Euro ',
            'numeric_code' => '947',
            'minor_unit'   => 2,
        ),
        'CHW' => array(
            'entity'       => 'SWITZERLAND',
            'currency'     => 'WIR Franc ',
            'numeric_code' => '948',
            'minor_unit'   => 2,
        ),
        'SYP' => array(
            'entity'       => 'SYRIAN ARAB REPUBLIC',
            'currency'     => 'Syrian Pound',
            'numeric_code' => '760',
            'minor_unit'   => 2,
        ),
        'TWD' => array(
            'entity'       => 'TAIWAN, PROVINCE OF CHINA',
            'currency'     => 'New Taiwan Dollar',
            'numeric_code' => '901',
            'minor_unit'   => 2,
        ),
        'TJS' => array(
            'entity'       => 'TAJIKISTAN',
            'currency'     => 'Somoni ',
            'numeric_code' => '972',
            'minor_unit'   => 2,
        ),
        'TZS' => array(
            'entity'       => 'TANZANIA, UNITED REPUBLIC OF',
            'currency'     => 'Tanzanian Shilling',
            'numeric_code' => '834',
            'minor_unit'   => 2,
        ),
        'THB' => array(
            'entity'       => 'THAILAND',
            'currency'     => 'Baht',
            'numeric_code' => '764',
            'minor_unit'   => 2,
        ),
        'TOP' => array(
            'entity'       => 'TONGA',
            'currency'     => 'Pa?anga',
            'numeric_code' => '776',
            'minor_unit'   => 2,
        ),
        'TTD' => array(
            'entity'       => 'TRINIDAD AND TOBAGO',
            'currency'     => 'Trinidad and Tobago Dollar',
            'numeric_code' => '780',
            'minor_unit'   => 2,
        ),
        'TND' => array(
            'entity'       => 'TUNISIA',
            'currency'     => 'Tunisian Dinar',
            'numeric_code' => '788',
            'minor_unit'   => 3,
        ),
        'TRY' => array(
            'entity'       => 'TURKEY',
            'currency'     => 'Turkish Lira ',
            'numeric_code' => '949',
            'minor_unit'   => 2,
        ),
        'TMT' => array(
            'entity'       => 'TURKMENISTAN',
            'currency'     => 'New Manat',
            'numeric_code' => '934',
            'minor_unit'   => 2,
        ),
        'UGX' => array(
            'entity'       => 'UGANDA',
            'currency'     => 'Uganda Shilling',
            'numeric_code' => '800',
            'minor_unit'   => 2,
        ),
        'UAH' => array(
            'entity'       => 'UKRAINE',
            'currency'     => 'Hryvnia',
            'numeric_code' => '980',
            'minor_unit'   => 2,
        ),
        'AED' => array(
            'entity'       => 'UNITED ARAB EMIRATES',
            'currency'     => 'UAE Dirham',
            'numeric_code' => '784',
            'minor_unit'   => 2,
        ),
        'USN' => array(
            'entity'       => 'UNITED STATES',
            'currency'     => 'US Dollar (Next day) ',
            'numeric_code' => '997',
            'minor_unit'   => 2,
        ),
        'USS' => array(
            'entity'       => 'UNITED STATES',
            'currency'     => 'US Dollar (Same day)',
            'numeric_code' => '998',
            'minor_unit'   => 2,
        ),
        'UYU' => array(
            'entity'       => 'URUGUAY',
            'currency'     => 'Peso Uruguayo',
            'numeric_code' => '858',
            'minor_unit'   => 2,
        ),
        'UYI' => array(
            'entity'       => 'URUGUAY',
            'currency'     => 'Uruguay Peso en Unidades Indexadas (URUIURUI) ',
            'numeric_code' => '940',
            'minor_unit'   => 0,
        ),
        'UZS' => array(
            'entity'       => 'UZBEKISTAN',
            'currency'     => 'Uzbekistan Sum',
            'numeric_code' => '860',
            'minor_unit'   => 2,
        ),
        'VUV' => array(
            'entity'       => 'VANUATU',
            'currency'     => 'Vatu',
            'numeric_code' => '548',
            'minor_unit'   => 0,
        ),
        'VEF' => array(
            'entity'       => 'VENEZUELA, BOLIVARIAN REPUBLIC OF',
            'currency'     => 'Bolivar Fuerte ',
            'numeric_code' => '937',
            'minor_unit'   => 2,
        ),
        'VND' => array(
            'entity'       => 'VIET NAM',
            'currency'     => 'Dong',
            'numeric_code' => '704',
            'minor_unit'   => 0,
        ),
        'YER' => array(
            'entity'       => 'YEMEN',
            'currency'     => 'Yemeni Rial',
            'numeric_code' => '886',
            'minor_unit'   => 2,
        ),
        'ZMK' => array(
            'entity'       => 'ZAMBIA',
            'currency'     => 'Zambian Kwacha',
            'numeric_code' => '894',
            'minor_unit'   => 2,
        ),
        'ZWL' => array(
            'entity'       => 'ZIMBABWE',
            'currency'     => 'Zimbabwe Dollar ',
            'numeric_code' => '932',
            'minor_unit'   => 2,
        ),
        'XBA' => array(
            'entity'       => 'ZZ01_Bond Markets Unit European_EURCO',
            'currency'     => 'Bond Markets Unit European Composite Unit (EURCO)',
            'numeric_code' => '955',
            'minor_unit'   => 0,
        ),
        'XBB' => array(
            'entity'       => 'ZZ02_Bond Markets Unit European_EMU-6',
            'currency'     => 'Bond Markets Unit European Monetary Unit (E.M.U.-6) ',
            'numeric_code' => '956',
            'minor_unit'   => 0,
        ),
        'XBC' => array(
            'entity'       => 'ZZ03_Bond Markets Unit European_EUA-9',
            'currency'     => 'Bond Markets Unit European Unit of Account 9 (E.U.A.-9)',
            'numeric_code' => '957',
            'minor_unit'   => 0,
        ),
        'XBD' => array(
            'entity'       => 'ZZ04_Bond Markets Unit European_EUA-17',
            'currency'     => 'Bond Markets Unit European Unit of Account 17 (E.U.A.-17)',
            'numeric_code' => '958',
            'minor_unit'   => 0,
        ),
        'XFU' => array(
            'entity'       => 'ZZ05_UIC-Franc',
            'currency'     => 'UIC-Franc',
            'numeric_code' => 'Nil',
            'minor_unit'   => 0,
        ),
        'XTS' => array(
            'entity'       => 'ZZ06_Testing_Code',
            'currency'     => 'Codes specifically reserved for testing purposes',
            'numeric_code' => '963',
            'minor_unit'   => 0,
        ),
        'XXX' => array(
            'entity'       => 'ZZ07_No_Currency',
            'currency'     => 'The codes assigned for transactions where no currency is involved',
            'numeric_code' => '999',
            'minor_unit'   => 0,
        ),
        'XAU' => array(
            'entity'       => 'ZZ08_Gold',
            'currency'     => 'Gold',
            'numeric_code' => '959',
            'minor_unit'   => 0,
        ),
        'XPD' => array(
            'entity'       => 'ZZ09_Palladium',
            'currency'     => 'Palladium',
            'numeric_code' => '964',
            'minor_unit'   => 0,
        ),
        'XPT' => array(
            'entity'       => 'ZZ10_Platinum',
            'currency'     => 'Platinum',
            'numeric_code' => '962',
            'minor_unit'   => 0,
        ),
        'XAG' => array(
            'entity'       => 'ZZ11_Silver',
            'currency'     => 'Silver',
            'numeric_code' => '961',
            'minor_unit'   => 0,
        ),
    );

    /**
     * Gets amount of minor units for requested currency
     *
     * @param string $currency Currency to use
     *
     * @return string Minor units
     */
    public function getMinorUnits($currency)
    {
        if (array_key_exists($currency, self::$_currencies) && ($details = self::$_currencies[$currency])) {
            return $details['minor_unit'];
        }

        //default 2
        return 2;
    }

    /**
     * Get the amount in minor units
     *
     * @param $amount   The amount to convert to minor_units
     * @param $currency The currency to determine minor_units
     *
     * @return float The amount in minor units according to currency
     */
    public function toMinorUnits($amount, $currency)
    {
        if ($minor_units = $this->getMinorUnits($currency)) {
            $amount = round($amount * (10 ** $minor_units));
        }

        return $amount;
    }
}
