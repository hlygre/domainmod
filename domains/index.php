<?php
/**
 * /domains/index.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2019 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
?>
<?php //@formatter:off
require_once __DIR__ . '/../_includes/start-session.inc.php';
require_once __DIR__ . '/../_includes/init.inc.php';
require_once DIR_INC . '/config.inc.php';
require_once DIR_INC . '/software.inc.php';
require_once DIR_ROOT . '/vendor/autoload.php';

$deeb = DomainMOD\Database::getInstance();
$system = new DomainMOD\System();
$layout = new DomainMOD\Layout();
$date = new DomainMOD\Date();
$time = new DomainMOD\Time();
$form = new DomainMOD\Form();
$assets = new DomainMOD\Assets();
$currency = new DomainMOD\Currency();
$customField = new DomainMOD\CustomField();
$segment = new DomainMOD\Segment();
$validate = new DomainMOD\Validate();
$sanitize = new DomainMOD\Sanitize();
$unsanitize = new DomainMOD\Unsanitize();

require_once DIR_INC . '/head.inc.php';
require_once DIR_INC . '/debug.inc.php';
require_once DIR_INC . '/settings/domains-main.inc.php';

$system->authCheck();
$pdo = $deeb->cnxx;

$export_data = (int) $_GET['export_data'];
$pcid = (int) $_REQUEST['pcid'];
$oid = (int) $_REQUEST['oid'];
$dnsid = (int) $_REQUEST['dnsid'];
$ipid = (int) $_REQUEST['ipid'];
$whid = (int) $_REQUEST['whid'];
$rid = (int) $_REQUEST['rid'];
$raid = (int) $_REQUEST['raid'];
$tld = $sanitize->text($_REQUEST['tld']);
$segid = (int) $_REQUEST['segid'];
$is_active = $_REQUEST['is_active'];
$search_for = urlencode($_REQUEST['search_for']);
$from_dropdown = (int) $_REQUEST['from_dropdown'];
$expand = (int) $_REQUEST['expand'];
$daterange = $_REQUEST['daterange'];

list($new_start_date, $new_end_date) = $date->splitAndCheckRange($daterange);

if ($_SESSION['s_system_large_mode'] == '1') {

    $result_limit = $_REQUEST['result_limit'];
    $sort_by = $_REQUEST['sort_by'];
    $numBegin = $_REQUEST['numBegin'];
    $begin = $_REQUEST['begin'];
    $num = $_REQUEST['num'];

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $from_dropdown = 0;

    $date = new DomainMOD\Date();

    if ($new_start_date > $new_end_date) {

        $_SESSION['s_message_danger'] .= 'The date range is invalid<BR>';

    }

}

if ($export_data !== 1) {

    if ($from_dropdown !== 1) {

        if ($search_for != "") {

            $_SESSION['s_search_for'] = $search_for;

        } elseif ($numBegin != "" && $_SESSION['s_system_large_mode'] == '1') {

            // $_SESSION['s_search_for'] = $_SESSION['s_search_for'];

        } else {

            $_SESSION['s_search_for'] = "";

        }

        if ($new_start_date != '' && $new_start_date != 'YYYY-MM-DD') {

            $_SESSION['s_start_date'] = $new_start_date;
            $_SESSION['s_end_date'] = $new_end_date;

        } elseif ($numBegin != "" && $_SESSION['s_system_large_mode'] == '1') {

            // $_SESSION['s_start_date'] = $_SESSION['s_start_date'];
            // $_SESSION['s_end_date'] = $_SESSION['s_end_date'];

        } else {

            $_SESSION['s_start_date'] = 'YYYY-MM-DD';
            $_SESSION['s_end_date'] = 'YYYY-MM-DD';

        }

    }

}

if ($_SESSION['s_start_date'] == '') $_SESSION['s_start_date'] = 'YYYY-MM-DD';
if ($_SESSION['s_end_date'] == '') $_SESSION['s_end_date'] = 'YYYY-MM-DD';

if ($_SESSION['s_system_large_mode'] == '1') {

    if ($result_limit == "") $result_limit = $_SESSION['s_number_of_domains'];

}

if ($is_active == "") $is_active = "LIVE";

if ($tld && $tld != '') {

    if ($validate->tld($tld) === false || $tld == '0') {

        $tld = '';

    }

}

if ($is_active == "0") { $is_active_string = " AND d.active = '0' ";
} elseif ($is_active == "1") { $is_active_string = " AND d.active = '1' ";
} elseif ($is_active == "2") { $is_active_string = " AND d.active = '2' ";
} elseif ($is_active == "3") { $is_active_string = " AND d.active = '3' ";
} elseif ($is_active == "4") { $is_active_string = " AND d.active = '4' ";
} elseif ($is_active == "5") { $is_active_string = " AND d.active = '5' ";
} elseif ($is_active == "6") { $is_active_string = " AND d.active = '6' ";
} elseif ($is_active == "7") { $is_active_string = " AND d.active = '7' ";
} elseif ($is_active == "8") { $is_active_string = " AND d.active = '8' ";
} elseif ($is_active == "9") { $is_active_string = " AND d.active = '9' ";
} elseif ($is_active == "10") { $is_active_string = " AND d.active = '10' ";
} elseif ($is_active == "LIVE") { $is_active_string = " AND d.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
} elseif ($is_active == "ALL") { $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
} else { $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
}

if ($segid !== 0) {

    $temp_segment = $segment->getSegment($segid);

    $segid_string = " AND d.domain IN ($temp_segment)";

} else {

    $segid_string = "";
}

if ($pcid !== 0) {
    $pcid_string = " AND d.cat_id = '$pcid' ";
} else {
    $pcid_string = "";
}

if ($oid !== 0) {
    $oid_string = " AND o.id = '$oid' ";
} else {
    $oid_string = "";
}

if ($dnsid !== 0) {
    $dnsid_string = " AND dns.id = '$dnsid' ";
} else {
    $dnsid_string = "";
}

if ($ipid !== 0) {
    $ipid_string = " AND ip.id = '$ipid' ";
} else {
    $ipid_string = "";
}

if ($whid !== 0) {
    $whid_string = " AND h.id = '$whid' ";
} else {
    $whid_string = "";
}

if ($rid !== 0) {
    $rid_string = " AND r.id = '$rid' ";
} else {
    $rid_string = "";
}

if ($raid !== 0) {
    $raid_string = " AND d.account_id = '$raid' ";
} else {
    $raid_string = "";
}

if ($tld != "") {
    $tld_string = " AND d.tld = '$tld' ";
} else {
    $tld_string = "";
}

if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
    $range_string = " AND (d.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND d.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
} else {
    $range_string = "";
}

if ($_SESSION['s_search_for'] != "") {
    $search_string = " AND d.domain LIKE '%" . $_SESSION['s_search_for'] . "%' ";
} else {
    $search_string = "";
}

if ($_SESSION['s_system_large_mode'] == '1') {

    if ($sort_by == "") $sort_by = "ed_a";

    if ($sort_by == "ed_a") { $sort_by_string = " ORDER BY d.expiry_date asc, d.domain asc ";
    } elseif ($sort_by == "ed_d") { $sort_by_string = " ORDER BY d.expiry_date desc, d.domain asc ";
    } elseif ($sort_by == "pc_a") { $sort_by_string = " ORDER BY cat.name asc ";
    } elseif ($sort_by == "pc_d") { $sort_by_string = " ORDER BY cat.name desc ";
    } elseif ($sort_by == "dn_a") { $sort_by_string = " ORDER BY d.domain asc ";
    } elseif ($sort_by == "dn_d") { $sort_by_string = " ORDER BY d.domain desc ";
    } elseif ($sort_by == "df_a") { $sort_by_string = " ORDER BY d.total_cost asc ";
    } elseif ($sort_by == "df_d") { $sort_by_string = " ORDER BY d.total_cost desc ";
    } elseif ($sort_by == "dns_a") { $sort_by_string = " ORDER BY dns.name asc ";
    } elseif ($sort_by == "dns_d") { $sort_by_string = " ORDER BY dns.name desc ";
    } elseif ($sort_by == "tld_a") { $sort_by_string = " ORDER BY d.tld asc ";
    } elseif ($sort_by == "tld_d") { $sort_by_string = " ORDER BY d.tld desc ";
    } elseif ($sort_by == "ip_a") { $sort_by_string = " ORDER BY ip.name asc, ip.ip asc";
    } elseif ($sort_by == "ip_d") { $sort_by_string = " ORDER BY ip.name desc, ip.ip desc";
    } elseif ($sort_by == "wh_a") { $sort_by_string = " ORDER BY h.name asc";
    } elseif ($sort_by == "wh_d") { $sort_by_string = " ORDER BY h.name desc";
    } elseif ($sort_by == "o_a") { $sort_by_string = " ORDER BY o.name asc, d.domain asc ";
    } elseif ($sort_by == "o_d") { $sort_by_string = " ORDER BY o.name desc, d.domain asc ";
    } elseif ($sort_by == "r_a") { $sort_by_string = " ORDER BY r.name asc, d.domain asc ";
    } elseif ($sort_by == "r_d") { $sort_by_string = " ORDER BY r.name desc, d.domain asc ";
    } elseif ($sort_by == "ra_a") { $sort_by_string = " ORDER BY r.name asc, d.domain asc ";
    } elseif ($sort_by == "ra_d") { $sort_by_string = " ORDER BY r.name desc, d.domain asc ";
    } else { $sort_by_string = " ORDER BY d.expiry_date asc, d.domain asc "; }

} else {

    $sort_by_string = '';

}

$dfd_columns = $customField->getCustomFieldsSql('domain_fields', 'dfd');

$sql = "SELECT d.id, d.domain, d.tld, d.expiry_date, d.total_cost, d.function, d.notes, d.autorenew, d.privacy, d.creation_type_id, d.created_by, d.active, d.insert_time, d.update_time, ra.id AS ra_id, ra.username, r.id AS r_id, r.name AS registrar_name, o.id AS o_id, o.name AS owner_name, cat.id AS pcid, cat.name AS category_name, cat.stakeholder, f.id AS f_id, f.initial_fee, f.renewal_fee, f.transfer_fee, f.privacy_fee, f.misc_fee, c.currency, cc.conversion, dns.id as dnsid, dns.name as dns_name, ip.id AS ipid, ip.ip AS ip, ip.name AS ip_name, ip.rdns, h.id AS whid, h.name AS wh_name" . $dfd_columns . "
        FROM domains AS d, registrar_accounts AS ra, registrars AS r, owners AS o, categories AS cat, fees AS f, currencies AS c, currency_conversions AS cc, dns AS dns, ip_addresses AS ip, hosting AS h, domain_field_data AS dfd
        WHERE d.account_id = ra.id
          AND ra.registrar_id = r.id
          AND ra.owner_id = o.id
          AND d.cat_id = cat.id
          AND d.fee_id = f.id
          AND d.dns_id = dns.id
          AND d.ip_id = ip.id
          AND d.hosting_id = h.id
          AND f.currency_id = c.id
          AND c.id = cc.currency_id
          AND d.id = dfd.domain_id
          AND cc.user_id = '" . $_SESSION['s_user_id'] . "'
          $is_active_string
          $segid_string
          $pcid_string
          $oid_string
          $dnsid_string
          $ipid_string
          $whid_string
          $rid_string
          $raid_string
          $range_string
          $tld_string
          $search_string
          $sort_by_string";
$_SESSION['s_raw_list_type'] = 'domains';
$_SESSION['s_raw_list_query'] = $sql;

// This query is identical to the main query, except that it only does a count
$total_rows = $pdo->query("
    SELECT count(*)
    FROM domains AS d, registrar_accounts AS ra, registrars AS r, owners AS o, categories AS cat, fees AS f, currencies AS c, currency_conversions AS cc, dns AS dns, ip_addresses AS ip, hosting AS h, domain_field_data AS dfd
    WHERE d.account_id = ra.id
      AND ra.registrar_id = r.id
      AND ra.owner_id = o.id
      AND d.cat_id = cat.id
      AND d.fee_id = f.id
      AND d.dns_id = dns.id
      AND d.ip_id = ip.id
      AND d.hosting_id = h.id
      AND f.currency_id = c.id
      AND c.id = cc.currency_id
      AND d.id = dfd.domain_id
      AND cc.user_id = '" . $_SESSION['s_user_id'] . "'
      $is_active_string
      $segid_string
      $pcid_string
      $oid_string
      $dnsid_string
      $ipid_string
      $whid_string
      $rid_string
      $raid_string
      $range_string
      $tld_string
      $search_string
      $sort_by_string")->fetchColumn();

$grand_total = $pdo->query("
    SELECT SUM(d.total_cost * cc.conversion)
    FROM domains AS d, registrar_accounts AS ra, registrars AS r, owners AS o, categories AS cat, fees AS f, currencies AS c, currency_conversions AS cc, dns AS dns, ip_addresses AS ip, hosting AS h
    WHERE d.account_id = ra.id
      AND ra.registrar_id = r.id
      AND ra.owner_id = o.id
      AND d.cat_id = cat.id
      AND d.fee_id = f.id
      AND d.dns_id = dns.id
      AND d.ip_id = ip.id
      AND d.hosting_id = h.id
      AND f.currency_id = c.id
      AND c.id = cc.currency_id
      AND cc.user_id = '" . $_SESSION['s_user_id'] . "'
      $is_active_string
      $segid_string
      $pcid_string
      $oid_string
      $dnsid_string
      $ipid_string
      $whid_string
      $rid_string
      $raid_string
      $range_string
      $tld_string
      $search_string")->fetchColumn();

$grand_total = $currency->format($grand_total, $_SESSION['s_default_currency_symbol'],
    $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

if ($segid !== 0) {

    $result = $pdo->query($sql)->fetchAll();

    $active_domains = "'";
    foreach ($result as $row) {

        $active_domains .= $row->domain . "', '";

    }
    $active_domains .= "'";
    $active_domains = substr($active_domains, 0, -4);

    $stmt = $pdo->prepare("
        UPDATE segment_data
        SET filtered = '0'
        WHERE active = '1'
          AND segment_id = :segid");
    $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
    $stmt->execute();

    if ($active_domains) {

        $stmt = $pdo->prepare("
            UPDATE segment_data
            SET filtered = '1'
            WHERE active = '1'
              AND segment_id = :segid
              AND domain NOT IN (" . $active_domains . ")");
        $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
        $stmt->execute();

    }

    $stmt = $pdo->prepare("
        UPDATE segment_data
        SET filtered = '1'
        WHERE active = '1'
          AND segment_id = :segid
          AND domain NOT LIKE '%" . $search_for . "%'");
    $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
    $stmt->execute();

}

if ($export_data === 1) {

    $result = $pdo->query($sql)->fetchAll();

    $export = new DomainMOD\Export();
    $export_file = $export->openFile('domain_results', strtotime($time->stamp()));

    $row_contents = array('Domain Search Results Export');
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    if ($segid === 0) {

        $row_contents = array(
            'Total Cost:',
            $grand_total,
            $_SESSION['s_default_currency']
        );
        $export->writeRow($export_file, $row_contents);

        $row_contents = array(
            'Number of Domains:',
            $total_rows
        );
        $export->writeRow($export_file, $row_contents);

        $export->writeBlankRow($export_file);

    } else {

        $row_contents = array(
            'Total Cost:',
            $grand_total,
            $_SESSION['s_default_currency']
        );
        $export->writeRow($export_file, $row_contents);

        $export->writeBlankRow($export_file);

    }

    if ($tld != "") {

        $row_contents = array(
            'TLD',
            '.' . $tld
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($segid !== 0) {

        $stmt = $pdo->prepare("
            SELECT count(*)
            FROM segment_data
            WHERE segment_id = :segid
              AND inactive = '1'");
        $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
        $stmt->execute();
        $totalrows_inactive = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT count(*)
            FROM segment_data
            WHERE segment_id = :segid
              AND missing = '1'");
        $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
        $stmt->execute();
        $totalrows_missing = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT count(*)
            FROM segment_data
            WHERE segment_id = :segid
              AND filtered = '1'");
        $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
        $stmt->execute();
        $totalrows_filtered = $stmt->fetchColumn();

        if ($segid !== 0) {

            $number_of_domains = $segment->getNumberOfDomains($segid);

        }

        $row_contents = array('[Segment Results]');
        $export->writeRow($export_file, $row_contents);

        $temp_name = $segment->getName($segid);

        $row_contents = array(
            'Segment Filter:',
            $temp_name
        );
        $export->writeRow($export_file, $row_contents);

        $row_contents = array(
            'Domains in Segment:',
            number_format($number_of_domains)
        );
        $export->writeRow($export_file, $row_contents);

        $row_contents = array(
            'Matching Domains:',
            $total_rows
        );
        $export->writeRow($export_file, $row_contents);

        if ($totalrows_inactive > 0) {

            $row_contents = array(
                'Matching But Inactive Domains:',
                number_format($totalrows_inactive)
            );
            $export->writeRow($export_file, $row_contents);

        }

        if ($totalrows_filtered > 0) {

            $row_contents = array(
                'Matching But Filtered Domains:',
                number_format($totalrows_filtered)
            );
            $export->writeRow($export_file, $row_contents);

        }

        if ($totalrows_missing > 0) {

            $row_contents = array(
                'Missing Domains:',
                number_format($totalrows_missing)
            );
            $export->writeRow($export_file, $row_contents);

        }

    }

    $row_contents = array('[Search Filters]');
    $export->writeRow($export_file, $row_contents);

    if ($_SESSION['s_search_for'] != "") {

        $row_contents = array(
            'Keyword Search:',
            $_SESSION['s_search_for']
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($rid > 0) {

        $temp_registrar_name = $assets->getRegistrar($rid);

        $row_contents = array(
            'Registrar:',
            $temp_registrar_name
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($raid > 0) {

        $stmt = $pdo->prepare("
            SELECT r.name AS registrar_name, o.name AS owner_name, ra.username
            FROM registrar_accounts AS ra, registrars AS r, owners AS o
            WHERE ra.registrar_id = r.id
              AND ra.owner_id = o.id
              AND ra.id = :raid");
        $stmt->bindValue('raid', $raid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ($result) {

            $row_contents = array(
                'Registrar Account:',
                $result->registrar_name . " - " . $result->owner_name . " - " . $result->username
            );
            $export->writeRow($export_file, $row_contents);

        }

    }

    if ($dnsid > 0) {

        $temp_dns_name = $assets->getDnsName($dnsid);

        $row_contents = array(
            'DNS Profile:',
            $temp_dns_name
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($ipid > 0) {

        list($temp_ip, $temp_ip_name) = $assets->getIpAndName($ipid);

        $row_contents = array(
            'IP Address:',
            $temp_ip_name . ' (' . $temp_ip . ')'
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($whid > 0) {

        $temp_hosting_name = $assets->getHost($whid);

        $row_contents = array(
            'Web Host:',
            $temp_hosting_name
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($pcid > 0) {

        $temp_category = $assets->getCat($pcid);

        $row_contents = array(
            'Category:',
            $temp_category
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($oid > 0) {

        $temp_owner = $assets->getOwner($oid);

        $row_contents = array(
            'Owner:',
            $temp_owner
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {

        $row_contents = array(
            'Expiry Date Range:',
            $_SESSION['s_start_date']  . " to " . $_SESSION['s_end_date']
        );
        $export->writeRow($export_file, $row_contents);

    }

    unset($row_contents);
    $count = 0;

    $row_contents[$count++] = 'Domain Status:';

    if ($is_active == "ALL") {

        $row_contents[$count++] = 'ALL';

    } elseif ($is_active == "LIVE" || $is_active == "") {

        $row_contents[$count++] = 'LIVE (Active / Transfers / Pending)';

    } elseif ($is_active == "0") {

        $row_contents[$count++] = 'Expired';

    } elseif ($is_active == "1") {

        $row_contents[$count++] = 'Active';

    } elseif ($is_active == "2") {

        $row_contents[$count++] = 'Pending (Transfer)';

    } elseif ($is_active == "3") {

        $row_contents[$count++] = 'Pending (Renewal)';

    } elseif ($is_active == "4") {

        $row_contents[$count++] = 'Pending (Other)';

    } elseif ($is_active == "5") {

        $row_contents[$count++] = 'Pending (Registration)';

    } elseif ($is_active == "10") {

        $row_contents[$count++] = 'Sold';

    }
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    unset($row_contents);
    $count = 0;

    $row_contents[$count++] = "Domain Status";
    $row_contents[$count++] = "Expiry Date";
    $row_contents[$count++] = "Initial Fee";
    $row_contents[$count++] = "Renewal Fee";
    $row_contents[$count++] = "Transfer Fee";
    $row_contents[$count++] = "Privacy Fee";
    $row_contents[$count++] = "Misc Fee";
    $row_contents[$count++] = "Total Yearly Cost";
    $row_contents[$count++] = "Domain";
    $row_contents[$count++] = "TLD";
    $row_contents[$count++] = "Function";
    $row_contents[$count++] = "Renewal Status";
    $row_contents[$count++] = "WHOIS Status";
    $row_contents[$count++] = "Registrar";
    $row_contents[$count++] = "Registrar Account";
    $row_contents[$count++] = "Username";
    $row_contents[$count++] = "DNS Profile";
    $row_contents[$count++] = "IP Address Name";
    $row_contents[$count++] = "IP Address";
    $row_contents[$count++] = "IP Address rDNS";
    $row_contents[$count++] = "Web Host";
    $row_contents[$count++] = "Category";
    $row_contents[$count++] = "Category Stakeholder";
    $row_contents[$count++] = "Owner";
    $row_contents[$count++] = "Notes";
    $row_contents[$count++] = "Creation Type";
    $row_contents[$count++] = "Created By";
    $row_contents[$count++] = "Inserted";
    $row_contents[$count++] = "Updated";
    $row_contents[$count++] = "CUSTOM FIELDS";

    $result_field = $pdo->query("
        SELECT `name`
        FROM domain_fields
        ORDER BY `name` ASC")->fetchAll();

    if ($result_field) {

        foreach ($result_field as $row_field) {

            $row_contents[$count++] = $row_field->name;

        }

    }

    $export->writeRow($export_file, $row_contents);

    foreach ($result as $row) {

        $temp_initial_fee = $row->initial_fee * $row->conversion;
        $temp_renewal_fee = $row->renewal_fee * $row->conversion;
        $temp_transfer_fee = $row->transfer_fee * $row->conversion;
        $temp_privacy_fee = $row->privacy_fee * $row->conversion;
        $temp_misc_fee = $row->misc_fee * $row->conversion;
        $temp_total_cost = $row->total_cost * $row->conversion;

        if ($row->active == "0") {
            $domain_status = "EXPIRED";
        } elseif ($row->active == "1") {
            $domain_status = "ACTIVE";
        } elseif ($row->active == "2") {
            $domain_status = "PENDING (TRANSFER)";
        } elseif ($row->active == "3") {
            $domain_status = "PENDING (RENEWAL)";
        } elseif ($row->active == "4") {
            $domain_status = "PENDING (OTHER)";
        } elseif ($row->active == "5") {
            $domain_status = "PENDING (REGISTRATION)";
        } elseif ($row->active == "10") {
            $domain_status = "SOLD";
        }

        if ($row->autorenew == "1") {
            $autorenew_status = "Auto Renewal";
        } elseif ($row->autorenew == "0") {
            $autorenew_status = "Manual Renewal";
        }

        if ($row->privacy == "1") {
            $privacy_status = "Private";
        } elseif ($row->privacy == "0") {
            $privacy_status = "Public";
        }

        $export_initial_fee = $currency->format($temp_initial_fee, $_SESSION['s_default_currency_symbol'],
            $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

        $export_renewal_fee = $currency->format($temp_renewal_fee, $_SESSION['s_default_currency_symbol'],
            $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

        $export_transfer_fee = $currency->format($temp_transfer_fee, $_SESSION['s_default_currency_symbol'],
            $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

        $export_privacy_fee = $currency->format($temp_privacy_fee, $_SESSION['s_default_currency_symbol'],
            $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

        $export_misc_fee = $currency->format($temp_misc_fee, $_SESSION['s_default_currency_symbol'],
            $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

        $export_total_cost = $currency->format($temp_total_cost, $_SESSION['s_default_currency_symbol'],
            $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

        unset($row_contents);
        $count = 0;

        $creation_type = $system->getCreationType($row->creation_type_id);

        $row_contents[$count++] = $domain_status;
        $row_contents[$count++] = $row->expiry_date;
        $row_contents[$count++] = $export_initial_fee;
        $row_contents[$count++] = $export_renewal_fee;
        $row_contents[$count++] = $export_transfer_fee;
        $row_contents[$count++] = $export_privacy_fee;
        $row_contents[$count++] = $export_misc_fee;
        $row_contents[$count++] = $export_total_cost;
        $row_contents[$count++] = $row->domain;
        $row_contents[$count++] = '.' . $row->tld;
        $row_contents[$count++] = $row->function;
        $row_contents[$count++] = $autorenew_status;
        $row_contents[$count++] = $privacy_status;
        $row_contents[$count++] = $row->registrar_name;
        $row_contents[$count++] = $row->registrar_name . ', ' . $row->owner_name . '(' . $row->username . ')';
        $row_contents[$count++] = $row->username;
        $row_contents[$count++] = $row->dns_name;
        $row_contents[$count++] = $row->ip_name;
        $row_contents[$count++] = $row->ip;
        $row_contents[$count++] = $row->rdns;
        $row_contents[$count++] = $row->wh_name;
        $row_contents[$count++] = $row->category_name;
        $row_contents[$count++] = $row->stakeholder;
        $row_contents[$count++] = $row->owner_name;
        $row_contents[$count++] = $row->notes;
        $row_contents[$count++] = $creation_type;
        if ($row->created_by == '0') {
            $row_contents[$count++] = 'Unknown';
        } else {
            $user = new DomainMOD\User();
            $row_contents[$count++] = $user->getFullName($row->created_by);
        }
        $row_contents[$count++] = $time->toUserTimezone($row->insert_time);
        $row_contents[$count++] = $time->toUserTimezone($row->update_time);
        $row_contents[$count++] = '';

        $dfd_columns_array = $customField->getCustomFields('domain_fields');

        if ($dfd_columns_array != "") {

            foreach ($dfd_columns_array as $column) {

                $row_contents[$count++] = $row->{$column};

            }

        }

        $export->writeRow($export_file, $row_contents);

    }

    $export->closeFile($export_file);

}
?>
<?php require_once DIR_INC . '/doctype.inc.php'; ?>
<html>
<head>
    <title><?php echo $layout->pageTitle($page_title); ?></title>
    <?php require_once DIR_INC . '/layout/head-tags.inc.php'; ?>
    <?php require_once DIR_INC . '/layout/date-range-picker-head.inc.php'; ?>
    <?php echo $layout->jumpMenu(); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
<?php require_once DIR_INC . '/layout/header.inc.php'; ?>
<?php
$result_supported = $pdo->query("
    SELECT `name`
    FROM api_registrars
    ORDER BY name ASC")->fetchAll();

$supported_registrars = '';
foreach ($result_supported as $row_supported) {

    $supported_registrars .= ', ' . $row_supported->name;

}
$supported_registrars = substr($supported_registrars, 2);

// Double check to make sure there are still no domains in the system
if ($_SESSION['s_has_domain'] == '0') {
    
    $queryB = new DomainMOD\QueryBuild();
    $sql_asset_check = $queryB->singleAsset('domains');
    $_SESSION['s_has_domain'] = $system->checkForRows($sql_asset_check);

}

if ($_SESSION['s_has_domain'] != '1' && $_SESSION['s_has_registrar'] == '1' && $_SESSION['s_has_registrar_account'] == '1') {

    $go_text1 = ' [<a href="' . $web_root . '/queue/intro.php">go</a>]';
    $go_text2 = ' [<a href="' . $web_root . '/bulk/">go</a>]';
    $go_text3 = ' [<a href="' . $web_root . '/domains/add.php">go</a>]';

} else {

    $go_text1 = '';
    $go_text2 = '';
    $go_text3 = '';

}

$subtext1 = 'Before you can start adding domains to DomainMOD you need to add at least one domain registrar and a domain registrar account.';

$subtext2 = 'Please see the <a target=\'_blank\' href=\'' . $web_root . '/docs/userguide/first-run/\'>First Run</a> section of the User Guide for more detailed information.';

if ($_SESSION['s_has_registrar'] != '1') {
    echo "<strong>0</strong> Domain Registrars found<BR><BR>";
    echo $subtext1 . '<BR><BR>';
    echo $subtext2 . '<BR><BR>';
    echo "<a href=\"../assets/add/registrar.php\">Click here to add a Domain Registrar &raquo;</a><BR>";
}

if ($_SESSION['s_has_registrar_account'] != '1' && $_SESSION['s_has_registrar'] == '1') {
    echo "<strong>0</strong> Domain Registrar Accounts found<BR><BR>";
    echo $subtext1 . '<BR><BR>';
    echo $subtext2 . '<BR><BR>';
    echo "<a href=\"../assets/add/registrar-account.php\">Click here to add a Domain Registrar Account &raquo;</a><BR>";
}

if ($_SESSION['s_has_domain'] != '1' && $_SESSION['s_has_registrar'] == '1' && $_SESSION['s_has_registrar_account'] == '1') {
    echo "<strong>0</strong> Domains found<BR><BR>";
    echo "Add a domain <a href='add.php'>manually</a>, add domains using the <a href='" . $web_root . "/queue/intro.php'>Domain Queue</a>, or see the <a target='_blank' href='../docs/userguide/adding-domains/'>Adding Domains</a> section of the User Guide for more options.<BR>";
}

if ($_SESSION['s_system_large_mode'] == '1') {

    $temp_numbegin = isset($_REQUEST['numBegin']) ? $_REQUEST['numBegin'] : 0;
    $temp_begin = isset($_REQUEST['begin']) ? $_REQUEST['begin'] : 0;
    $temp_num = isset($_REQUEST['num']) ? $_REQUEST['num'] : 0;

    $parameters = array($total_rows, 15, $result_limit, "&pcid=" . $pcid . "&oid=" . $oid . "&dnsid=" . $dnsid . "&ipid=" . $ipid . "&whid=" . $whid . "&rid=" . $rid . "&raid=" . $raid . "&daterange=" . $daterange . "&tld=" . $tld . "&segid=" . $segid . "&is_active=" . $is_active . "&result_limit=" . $result_limit . "&sort_by=" . $sort_by, $temp_numbegin, $temp_begin, $temp_num);
    $navigate = $layout->pageBrowser($parameters);
    $sql = $sql . $navigate[0];

}

$result = $pdo->query($sql)->fetchAll();

if ($segid !== 0) {

    $number_of_domains = $segment->getNumberOfDomains($segid);

}

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $expand === 1) {
    $box_type = 'expanded';
    $box_icon = 'minus';
} else {
    $box_type = 'collapsed';
    $box_icon = 'plus';
}

if ($_SESSION['s_has_domain'] == '1' && $_SESSION['s_has_registrar'] == '1' && $_SESSION['s_has_registrar_account'] == '1') {

    if ($_SESSION['s_system_large_mode'] != '1') {

        $result_limit = '';
        $sort_by = '';

    } ?>

    <div class="box box-default <?php echo $box_type; ?>-box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title" style="padding-top: 3px;">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-<?php echo $box_icon; ?>"></i></button>&nbsp;Advanced Filtering [<a href="<?php echo $web_root; ?>/domains/">reset filters</a>]
            </h3>
        </div>
        <div class="box-body">
            <?php
            echo $form->showFormTop('');

            // SEGMENT
            $result_segment = $pdo->query("
                SELECT id, `name`
                FROM segments
                ORDER BY `name` ASC")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'Segment Filter - OFF', 'null');
            foreach ($result_segment as $row_segment) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&segid=' . $row_segment->id . '&tld=' . $tld . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_segment->id, $row_segment->name, $segid);

            }
            echo $form->showDropdownBottom('');


            // REGISTRAR
            if ($is_active == "0") {
                $is_active_string = " AND d.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND d.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND d.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND d.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND d.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND d.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND d.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND d.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND d.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND d.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND d.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND d.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            } else {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            }

            if ($pcid !== 0) {
                $pcid_string = " AND d.cat_id = '$pcid' ";
            } else {
                $pcid_string = "";
            }
            if ($oid !== 0) {
                $oid_string = " AND d.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($dnsid !== 0) {
                $dnsid_string = " AND d.dns_id = '$dnsid' ";
            } else {
                $dnsid_string = "";
            }
            if ($ipid !== 0) {
                $ipid_string = " AND d.ip_id = '$ipid' ";
            } else {
                $ipid_string = "";
            }
            if ($whid !== 0) {
                $whid_string = " AND d.hosting_id = '$whid' ";
            } else {
                $whid_string = "";
            }
            if ($raid !== 0) {
                $raid_string = " AND d.account_id = '$raid' ";
            } else {
                $raid_string = "";
            }
            if ($tld != "") {
                $tld_string = " AND d.tld = '$tld' ";
            } else {
                $tld_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (d.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND d.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND d.domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND d.domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_registrar = $pdo->query("
                SELECT r.id, r.name
                FROM registrars AS r, domains AS d
                WHERE r.id = d.registrar_id
                  $is_active_string
                  $pcid_string
                  $oid_string
                  $dnsid_string
                  $ipid_string
                  $whid_string
                  $raid_string
                  $range_string
                  $tld_string
                  $search_string
                  $segment_string
                GROUP BY r.name
                ORDER BY r.name asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'Registrar - ALL', 'null');
            foreach ($result_registrar as $row_registrar) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $row_registrar->id . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_registrar->id, $row_registrar->name, $rid);

            }
            echo $form->showDropdownBottom('');


            // REGISTRAR ACCOUNT
            if ($is_active == "0") {
                $is_active_string = " AND d.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND d.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND d.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND d.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND d.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND d.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND d.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND d.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND d.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND d.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND d.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND d.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            } else {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            }

            if ($pcid !== 0) {
                $pcid_string = " AND d.cat_id = '$pcid' ";
            } else {
                $pcid_string = "";
            }
            if ($oid !== 0) {
                $oid_string = " AND d.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($dnsid !== 0) {
                $dnsid_string = " AND d.dns_id = '$dnsid' ";
            } else {
                $dnsid_string = "";
            }
            if ($ipid !== 0) {
                $ipid_string = " AND d.ip_id = '$ipid' ";
            } else {
                $ipid_string = "";
            }
            if ($whid !== 0) {
                $whid_string = " AND d.hosting_id = '$whid' ";
            } else {
                $whid_string = "";
            }
            if ($rid !== 0) {
                $rid_string = " AND d.registrar_id = '$rid' ";
            } else {
                $rid_string = "";
            }
            if ($tld != "") {
                $tld_string = " AND d.tld = '$tld' ";
            } else {
                $tld_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND d.domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND d.domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_account = $pdo->query("
                SELECT ra.id AS ra_id, ra.username, r.name AS r_name, o.name AS o_name
                FROM registrar_accounts AS ra, registrars AS r, owners AS o, domains AS d
                WHERE ra.registrar_id = r.id
                  AND ra.owner_id = o.id
                  AND ra.id = d.account_id
                  $is_active_string
                  $pcid_string
                  $oid_string
                  $dnsid_string
                  $ipid_string
                  $whid_string
                  $rid_string
                  $range_string
                  $tld_string
                  $search_string
                  $segment_string
                GROUP BY r.name, o.name, ra.username
                ORDER BY r.name asc, o.name asc, ra.username asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'Registrar Account - ALL', 'null');
            foreach ($result_account as $row_account) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $row_account->ra_id . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_account->ra_id, $row_account->r_name . ', ' . $row_account->o_name . ' (' . $row_account->username . ')', $raid);

            }
            echo $form->showDropdownBottom('');


            // DNS
            if ($is_active == "0") {
                $is_active_string = " AND d.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND d.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND d.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND d.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND d.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND d.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND d.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND d.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND d.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND d.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND d.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND d.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            } else {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            }

            if ($pcid !== 0) {
                $pcid_string = " AND d.cat_id = '$pcid' ";
            } else {
                $pcid_string = "";
            }
            if ($oid !== 0) {
                $oid_string = " AND d.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($ipid !== 0) {
                $ipid_string = " AND d.ip_id = '$ipid' ";
            } else {
                $ipid_string = "";
            }
            if ($whid !== 0) {
                $whid_string = " AND d.hosting_id = '$whid' ";
            } else {
                $whid_string = "";
            }
            if ($rid !== 0) {
                $rid_string = " AND d.registrar_id = '$rid' ";
            } else {
                $rid_string = "";
            }
            if ($raid !== 0) {
                $raid_string = " AND d.account_id = '$raid' ";
            } else {
                $raid_string = "";
            }
            if ($tld != "") {
                $tld_string = " AND d.tld = '$tld' ";
            } else {
                $tld_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (d.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND d.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND d.domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND d.domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_dns = $pdo->query("
                SELECT dns.id, dns.name
                FROM dns AS dns, domains AS d
                WHERE dns.id = d.dns_id
                  $is_active_string
                  $pcid_string
                  $oid_string
                  $ipid_string
                  $whid_string
                  $rid_string
                  $raid_string
                  $range_string
                  $tld_string
                  $search_string
                  $segment_string
                GROUP BY dns.name
                ORDER BY dns.name asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'DNS Profile - ALL', 'null');
            foreach ($result_dns as $row_dns) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $row_dns->id . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_dns->id, $row_dns->name, $dnsid);

            }
            echo $form->showDropdownBottom('');


            // IP ADDRESS
            if ($is_active == "0") {
                $is_active_string = " AND d.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND d.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND d.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND d.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND d.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND d.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND d.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND d.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND d.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND d.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND d.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND d.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            } else {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            }

            if ($pcid !== 0) {
                $pcid_string = " AND d.cat_id = '$pcid' ";
            } else {
                $pcid_string = "";
            }
            if ($oid !== 0) {
                $oid_string = " AND d.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($dnsid !== 0) {
                $dnsid_string = " AND d.dns_id = '$dnsid' ";
            } else {
                $dnsid_string = "";
            }
            if ($whid !== 0) {
                $whid_string = " AND d.hosting_id = '$whid' ";
            } else {
                $whid_string = "";
            }
            if ($rid !== 0) {
                $rid_string = " AND d.registrar_id = '$rid' ";
            } else {
                $rid_string = "";
            }
            if ($raid !== 0) {
                $raid_string = " AND d.account_id = '$raid' ";
            } else {
                $raid_string = "";
            }
            if ($tld != "") {
                $tld_string = " AND d.tld = '$tld' ";
            } else {
                $tld_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (d.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND d.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND d.domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_ip = $pdo->query("
                SELECT ip.id, ip.name, ip.ip
                FROM ip_addresses AS ip, domains AS d
                WHERE ip.id = d.ip_id
                  $is_active_string
                  $pcid_string
                  $oid_string
                  $dnsid_string
                  $whid_string
                  $rid_string
                  $raid_string
                  $range_string
                  $tld_string
                  $search_string
                  $segment_string
                GROUP BY ip.name
                ORDER BY ip.name asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'IP Address - ALL', 'null');
            foreach ($result_ip as $row_ip) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $row_ip->id . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_ip->id, $row_ip->name . ' (' . $row_ip->ip . ')', $ipid);

            }
            echo $form->showDropdownBottom('');


            // WEB HOSTING PROVIDER
            if ($is_active == "0") {
                $is_active_string = " AND d.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND d.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND d.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND d.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND d.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND d.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND d.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND d.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND d.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND d.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND d.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND d.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            } else {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            }

            if ($pcid !== 0) {
                $pcid_string = " AND d.cat_id = '$pcid' ";
            } else {
                $pcid_string = "";
            }
            if ($oid !== 0) {
                $oid_string = " AND d.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($dnsid !== 0) {
                $dnsid_string = " AND d.dns_id = '$dnsid' ";
            } else {
                $dnsid_string = "";
            }
            if ($ipid !== 0) {
                $ipid_string = " AND d.ip_id = '$ipid' ";
            } else {
                $ipid_string = "";
            }
            if ($rid !== 0) {
                $rid_string = " AND d.registrar_id = '$rid' ";
            } else {
                $rid_string = "";
            }
            if ($raid !== 0) {
                $raid_string = " AND d.account_id = '$raid' ";
            } else {
                $raid_string = "";
            }
            if ($tld != "") {
                $tld_string = " AND d.tld = '$tld' ";
            } else {
                $tld_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (d.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND d.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND d.domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_hosting = $pdo->query("
                SELECT h.id, h.name
                FROM hosting AS h, domains AS d
                WHERE h.id = d.hosting_id
                  $is_active_string
                  $pcid_string
                  $oid_string
                  $dnsid_string
                  $ipid_string
                  $rid_string
                  $raid_string
                  $range_string
                  $tld_string
                  $search_string
                  $segment_string
                GROUP BY h.name
                ORDER BY h.name asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'Web Hosting Provider - ALL', 'null');
            foreach ($result_hosting as $row_hosting) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $row_hosting->id . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_hosting->id, $row_hosting->name, $whid);

            }
            echo $form->showDropdownBottom('');


            // CATEGORY
            if ($is_active == "0") {
                $is_active_string = " AND d.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND d.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND d.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND d.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND d.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND d.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND d.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND d.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND d.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND d.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND d.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND d.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            } else {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            }

            if ($oid !== 0) {
                $oid_string = " AND d.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($dnsid !== 0) {
                $dnsid_string = " AND d.dns_id = '$dnsid' ";
            } else {
                $dnsid_string = "";
            }
            if ($ipid !== 0) {
                $ipid_string = " AND d.ip_id = '$ipid' ";
            } else {
                $ipid_string = "";
            }
            if ($whid !== 0) {
                $whid_string = " AND d.hosting_id = '$whid' ";
            } else {
                $whid_string = "";
            }
            if ($rid !== 0) {
                $rid_string = " AND d.registrar_id = '$rid' ";
            } else {
                $rid_string = "";
            }
            if ($raid !== 0) {
                $raid_string = " AND d.account_id = '$raid' ";
            } else {
                $raid_string = "";
            }
            if ($tld != "") {
                $tld_string = " AND d.tld = '$tld' ";
            } else {
                $tld_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (d.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND d.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND d.domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND d.domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_category = $pdo->query("
                SELECT c.id, c.name
                FROM categories AS c, domains AS d
                WHERE c.id = d.cat_id
                  $is_active_string
                  $oid_string
                  $dnsid_string
                  $ipid_string
                  $whid_string
                  $rid_string
                  $raid_string
                  $range_string
                  $tld_string
                  $search_string
                  $segment_string
                GROUP BY c.name
                ORDER BY c.name asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'Category - ALL', 'null');
            foreach ($result_category as $row_category) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $row_category->id . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_category->id, $row_category->name, $pcid);

            }
            echo $form->showDropdownBottom('');


            if ($is_active == "0") {
                $is_active_string = " AND d.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND d.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND d.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND d.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND d.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND d.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND d.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND d.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND d.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND d.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND d.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND d.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            } else {
                $is_active_string = " AND d.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            }

            if ($pcid !== 0) {
                $pcid_string = " AND d.cat_id = '$pcid' ";
            } else {
                $pcid_string = "";
            }
            if ($dnsid !== 0) {
                $dnsid_string = " AND d.dns_id = '$dnsid' ";
            } else {
                $dnsid_string = "";
            }
            if ($ipid !== 0) {
                $ipid_string = " AND d.ip_id = '$ipid' ";
            } else {
                $ipid_string = "";
            }
            if ($whid !== 0) {
                $whid_string = " AND d.hosting_id = '$whid' ";
            } else {
                $whid_string = "";
            }
            if ($rid !== 0) {
                $rid_string = " AND d.registrar_id = '$rid' ";
            } else {
                $rid_string = "";
            }
            if ($raid !== 0) {
                $raid_string = " AND d.account_id = '$raid' ";
            } else {
                $raid_string = "";
            }
            if ($tld != "") {
                $tld_string = " AND d.tld = '$tld' ";
            } else {
                $tld_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (d.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND d.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND d.domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND d.domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_owner = $pdo->query("
                SELECT o.id, o.name
                FROM owners AS o, domains AS d
                WHERE o.id = d.owner_id
                  $is_active_string
                  $pcid_string
                  $dnsid_string
                  $ipid_string
                  $whid_string
                  $rid_string
                  $raid_string
                  $range_string
                  $tld_string
                  $search_string
                  $segment_string
                GROUP BY o.name
                ORDER BY o.name asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'Owner - ALL', 'null');
            foreach ($result_owner as $row_owner) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $row_owner->id . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_owner->id, $row_owner->name, $oid);

            }
            echo $form->showDropdownBottom('');


            // TLD
            if ($is_active == "0") {
                $is_active_string = " WHERE active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " WHERE active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " WHERE active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " WHERE active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " WHERE active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " WHERE active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " WHERE active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " WHERE active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " WHERE active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " WHERE active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " WHERE active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " WHERE active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9') ";
            } elseif ($is_active == "ALL") {
                $is_active_string = " WHERE active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            } else {
                $is_active_string = " WHERE active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10') ";
            }

            if ($pcid !== 0) {
                $pcid_string = " AND cat_id = '$pcid' ";
            } else {
                $pcid_string = "";
            }
            if ($oid !== 0) {
                $oid_string = " AND owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($dnsid !== 0) {
                $dnsid_string = " AND dns_id = '$dnsid' ";
            } else {
                $dnsid_string = "";
            }
            if ($ipid !== 0) {
                $ipid_string = " AND ip_id = '$ipid' ";
            } else {
                $ipid_string = "";
            }
            if ($whid !== 0) {
                $whid_string = " AND hosting_id = '$whid' ";
            } else {
                $whid_string = "";
            }
            if ($rid !== 0) {
                $rid_string = " AND registrar_id = '$rid' ";
            } else {
                $rid_string = "";
            }
            if ($raid !== 0) {
                $raid_string = " AND account_id = '$raid' ";
            } else {
                $raid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (expiry_date >= '" . $_SESSION['s_start_date'] . "' AND expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_tld = $pdo->query("
                SELECT tld, count(*) AS total_tld_count
                FROM domains" .
                $is_active_string .
                  $pcid_string .
                  $oid_string .
                  $dnsid_string .
                  $ipid_string .
                  $whid_string .
                  $rid_string .
                  $raid_string .
                  $range_string .
                  $search_string .
                  $segment_string . " 
                GROUP BY tld 
                ORDER BY tld asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1', '', 'TLD - ALL', 'null');
            foreach ($result_tld as $row_tld) {

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $row_tld->tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $row_tld->tld, $row_tld->tld, $tld);

            }
            echo $form->showDropdownBottom('');


            // STATUS
            if ($pcid !== 0) {
                $pcid_string = " AND cat_id = '$pcid' ";
            } else {
                $pcid_string = "";
            }
            if ($oid !== 0) {
                $oid_string = " AND owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($dnsid !== 0) {
                $dnsid_string = " AND dns_id = '$dnsid' ";
            } else {
                $dnsid_string = "";
            }
            if ($ipid !== 0) {
                $ipid_string = " AND ip_id = '$ipid' ";
            } else {
                $ipid_string = "";
            }
            if ($whid !== 0) {
                $whid_string = " AND hosting_id = '$whid' ";
            } else {
                $whid_string = "";
            }
            if ($rid !== 0) {
                $rid_string = " AND registrar_id = '$rid' ";
            } else {
                $rid_string = "";
            }
            if ($raid !== 0) {
                $raid_string = " AND account_id = '$raid' ";
            } else {
                $raid_string = "";
            }
            if ($tld != "") {
                $tld_string = " AND tld = '$tld' ";
            } else {
                $tld_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (expiry_date >= '" . $_SESSION['s_start_date'] . "' AND expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for'] != "") {
                $search_string = " AND domain LIKE '%" . $_SESSION['s_search_for'] . "%'";
            } else {
                $search_string = "";
            }
            if ($segid !== 0) {
                $segment_string = " AND domain IN (SELECT domain FROM segment_data WHERE segment_id = '$segid') ";
            } else {
                $segment_string = "";
            }

            $result_active = $pdo->query("
                SELECT active, count(*) AS total_count
                FROM domains
                WHERE id != '0'
                  $pcid_string
                  $oid_string
                  $dnsid_string
                  $ipid_string
                  $whid_string
                  $rid_string
                  $raid_string
                  $range_string
                  $tld_string
                  $search_string
                  $segment_string
                GROUP BY active
                ORDER BY active asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=LIVE&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $is_active, '"Live" Domains (Active / Transfers / Pending)', 'LIVE');
            foreach ($result_active as $row_active) {

                if ($row_active->active == "0") {
                    $display_text = "Expired";
                } elseif ($row_active->active == "1") {
                    $display_text = "Active";
                } elseif ($row_active->active == "2") {
                    $display_text = "Pending (Transfer)";
                } elseif ($row_active->active == "3") {
                    $display_text = "Pending (Renewal)";
                } elseif ($row_active->active == "4") {
                    $display_text = "Pending (Other)";
                } elseif ($row_active->active == "5") {
                    $display_text = "Pending (Registration)";
                } elseif ($row_active->active == "10") {
                    $display_text = "Sold";
                }

                /* TODO: This needs to be fixed, but it's going to be a very big refactoring job, and this is the best temporary band-aid solution. */
                /*
                 * The problem is that the showDropdownOptionJump method uses the == comparison operator instead of ===,
                 * so 0 technically equals "LIVE", and Expired gets selected in the dropdown menu by default, since it
                 * overrides the actual "LIVE" option.
                 *
                 * It would be easy to just change the == operator to ===, however this may break other instances of
                 * the method, so to fix this properly it's going to take some time.
                 *
                 * This issue also exists on the main SSL page.
                 *
                 * START
                 */
                if ($row_active->active === 0) $row_active->active = '0';
                /*
                 * END
                 */

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $row_active->active . '&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $is_active, $display_text, $row_active->active);

            }
            echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=ALL&result_limit=' . $result_limit . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $is_active, 'ALL', 'ALL');
            echo $form->showDropdownBottom('');

            if ($_SESSION['s_system_large_mode'] == '1') {

                // NUMBER OF DOMAINS TO DISPLAY
                echo $form->showDropdownTopJump('', '', '', '');

                if ($_SESSION['s_number_of_domains'] != "10" && $_SESSION['s_number_of_domains'] != "50" && $_SESSION['s_number_of_domains'] != "100" && $_SESSION['s_number_of_domains'] != "500" && $_SESSION['s_number_of_domains'] != "1000" && $_SESSION['s_number_of_domains'] != "1000000") {

                    echo $form->showDropdownOptionJump('index.php.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=' . $_SESSION['s_number_of_domains'] . '&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $result_limit, $_SESSION['s_number_of_domains'], $_SESSION['s_number_of_domains']);

                }

                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=10&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $result_limit, '10', '10');
                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=50&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $result_limit, '50', '50');
                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=100&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $result_limit, '100', '100');
                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=500&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $result_limit, '500', '500');
                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=1000&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $result_limit, '1,000', '1000');
                echo $form->showDropdownOptionJump('index.php?pcid=' . $pcid . '&oid=' . $oid . '&dnsid=' . $dnsid . '&ipid=' . $ipid . '&whid=' . $whid . '&rid=' . $rid . '&raid=' . $raid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&tld=' . $tld . '&segid=' . $segid . '&is_active=' . $is_active . '&result_limit=1000000&sort_by=' . $sort_by . '&from_dropdown=1&expand=1&null=', $result_limit, 'ALL', '1000000');

                echo $form->showDropdownBottom('');

            } ?>


            <?php echo $form->showInputText('search_for', 'Domain Keyword Search', '', $_SESSION['s_search_for'], '100', '', '', '', ''); ?>

            <?php
            echo $form->showInputText('daterange', 'Expiring Between', '', $daterange, '23', '', '', '', '');

            echo $form->showInputHidden('pcid', $pcid);
            echo $form->showInputHidden('oid', $oid);
            echo $form->showInputHidden('dnsid', $dnsid);
            echo $form->showInputHidden('whid', $whid);
            echo $form->showInputHidden('rid', $rid);
            echo $form->showInputHidden('raid', $raid);
            echo $form->showInputHidden('tld', $tld);
            echo $form->showInputHidden('segid', $segid);
            echo $form->showInputHidden('result_limit', $result_limit);
            echo $form->showInputHidden('sort_by', $sort_by);
            echo $form->showInputHidden('is_active', $is_active);
            echo $form->showInputHidden('begin', '0');
            echo $form->showInputHidden('num', '1');
            echo $form->showInputHidden('numBegin', '1');
            echo $form->showSubmitButton('Apply Filters', '', '');
            ?>
            <a href="<?php echo $web_root; ?>/domains/"><?php echo $layout->showButton('button', 'Reset Filters'); ?></a><?php

            echo $form->showFormBottom(''); ?>

        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    <BR><?php

}

if ($segid !== 0) {

    $stmt = $pdo->prepare("
        SELECT count(*)
        FROM segment_data
        WHERE segment_id = :segid
          AND inactive = '1'");
    $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
    $stmt->execute();
    $totalrows_inactive = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT count(*)
        FROM segment_data
        WHERE segment_id = :segid
          AND missing = '1'");
    $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
    $stmt->execute();
    $totalrows_missing = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT count(*)
        FROM segment_data
        WHERE segment_id = :segid
          AND filtered = '1'");
    $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
    $stmt->execute();
    $totalrows_filtered = $stmt->fetchColumn();
    ?>
    <strong>Domains in Segment:</strong> <?php echo number_format($number_of_domains); ?><BR><BR>

    <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
        <strong>Matching Domains:</strong> <?php echo number_format($total_rows); ?><BR><BR>
    <?php } else { ?>
        <strong>Matching Domains:</strong> <?php echo number_format(count($result)); ?><BR><BR>
    <?php } ?>

    <?php if ($totalrows_inactive > 0) { ?>
        <strong>Matching But Inactive Domains:</strong> <?php echo number_format($totalrows_inactive); ?> [<a
            target="_blank" href="results.php?type=inactive&segid=<?php echo $segid; ?>">view</a>]<BR><BR>
    <?php } ?>
    <?php if ($totalrows_filtered > 0) { ?>
        <strong>Matching But Filtered Domains:</strong> <?php echo number_format($totalrows_filtered); ?> [<a
            target="_blank" href="results.php?type=filtered&segid=<?php echo $segid; ?>">view</a>]<BR><BR>
    <?php } ?>
    <?php if ($totalrows_missing > 0) { ?>
        <strong>Missing Domains:</strong> <?php echo number_format($totalrows_missing); ?> [<a
            target="_blank" href="results.php?type=missing&segid=<?php echo $segid; ?>">view</a>]<BR><BR>
    <?php }

}

if ($result) { ?>

    <a href="add.php"><?php echo $layout->showButton('button', 'Add Domain'); ?></a>
    <a href="<?php echo $web_root; ?>/queue/intro.php"><?php echo $layout->showButton('button', 'Add Domains To Queue'); ?></a>
    <a target="_blank" href="<?php echo $web_root; ?>/raw.php"><?php echo $layout->showButton('button', 'Raw List'); ?></a>
    <a href="index.php?<?php echo htmlentities($_SERVER['QUERY_STRING']); ?>&export_data=1"><?php echo $layout->showButton('button', 'Export'); ?></a>

    <?php if ($segid !== 0) { ?>
        <BR><BR><strong>Total Cost:</strong> <?php echo htmlentities($grand_total, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlentities($_SESSION['s_default_currency'], ENT_QUOTES, 'UTF-8'); ?>
        <BR><BR>
    <?php } else { ?>
        <BR><BR><strong>Total Cost:</strong> <?php echo htmlentities($grand_total, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlentities($_SESSION['s_default_currency'], ENT_QUOTES, 'UTF-8'); ?><BR>

        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
            <strong>Number of Domains:</strong> <?php echo number_format($total_rows); ?><BR><BR>
        <?php } else { ?>
            <strong>Number of Domains:</strong> <?php echo number_format(count($result)); ?><BR><BR>
        <?php } ?>

    <?php }

    if ($total_rows) {

        if ($_SESSION['s_system_large_mode'] == '1') {
            require DIR_INC . '/layout/pagination-large-mode.inc.php';
        } ?>

        <table id="<?php echo $slug; ?>" class="<?php echo $datatable_class; ?>">
            <thead>
            <tr>
                <?php if ($_SESSION['s_system_large_mode'] != '1') { ?>
                    <th width="20px"></th>
                <?php } ?>

                <th class="all">
                    <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                        <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                        ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                        echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php echo urlencode($new_end_date); ?>&segid=<?php
                        echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php echo urlencode($is_active); ?>&result_limit=<?php
                        echo urlencode($result_limit); ?>&sort_by=<?php
                        if ($sort_by == "dn_a") {
                            echo "dn_d";
                        } else {
                            echo "dn_a";
                        } ?>&from_dropdown=1" style="color:#000000;">Domain</a>
                    <?php } else { ?>
                            Domain
                    <?php } ?>
                </th>
                <?php if ($_SESSION['s_display_domain_expiry_date'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php
                            echo $dnsid; ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid;
                            ?>&raid=<?php echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php
                            echo urlencode($new_end_date); ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php
                            echo urlencode($is_active); ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                            if ($sort_by == "ed_a") {
                                echo "ed_d";
                            } else {
                                echo "ed_a";
                            }
                            ?>&from_dropdown=1" style="color:#000000;">Expiry</a>
                        <?php } else { ?>
                            Expiry
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_fee'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php
                            echo $dnsid; ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid;
                            ?>&raid=<?php echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php
                            echo urlencode($new_end_date); ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php
                            echo urlencode($is_active); ?>&result_limit=<?php echo urlencode($result_limit);
                            ?>&sort_by=<?php
                            if ($sort_by == "df_a") {
                                echo "df_d";
                            } else {
                                echo "df_a";
                            }
                            ?>&from_dropdown=1" style="color:#000000;">Fee (<?php echo $_SESSION['s_default_currency']; ?>)</a>
                        <?php } else { ?>
                                Fee (<?php echo $_SESSION['s_default_currency']; ?>)
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_tld'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                        <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                        ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                        echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php
                        echo urlencode($new_end_date); ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php
                        echo urlencode($is_active); ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                        if ($sort_by == "tld_a") {
                            echo "tld_d";
                        } else {
                            echo "tld_a";
                        } ?>&from_dropdown=1" style="color:#000000;">TLD</a>
                    <?php } else { ?>
                            TLD
                    <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_registrar'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                            ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                            echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php echo urlencode($new_end_date);
                            ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php echo urlencode($is_active);
                            ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                            if ($sort_by == "r_a") {
                                echo "r_d";
                            } else {
                                echo "r_a";
                            } ?>&from_dropdown=1" style="color:#000000;">Registrar</a>
                        <?php } else { ?>
                                Registrar
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_account'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                            ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                            echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php echo urlencode($new_end_date);
                            ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php echo urlencode($is_active);
                            ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                            if ($sort_by == "ra_a") {
                                echo "ra_d";
                            } else {
                                echo "ra_a";
                            } ?>&from_dropdown=1" style="color:#000000;">Account</a>
                        <?php } else { ?>
                                Account
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_dns'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                            ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                            echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php echo urlencode($new_end_date);
                            ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php echo urlencode($is_active);
                            ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                            if ($sort_by == "dns_a") {
                                echo "dns_d";
                            } else {
                                echo "dns_a";
                            } ?>&from_dropdown=1" style="color:#000000;">DNS</a>
                        <?php } else { ?>
                                DNS
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_ip'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                            ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                            echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php echo urlencode($new_end_date);
                            ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php echo urlencode($is_active);
                            ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                            if ($sort_by == "ip_a") {
                                echo "ip_d";
                            } else {
                                echo "ip_a";
                            } ?>&from_dropdown=1" style="color:#000000;">IP</a>
                        <?php } else { ?>
                                IP
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_host'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                            ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                            echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php echo urlencode($new_end_date);
                            ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php echo urlencode($is_active);
                            ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                            if ($sort_by == "wh_a") {
                                echo "wh_d";
                            } else {
                                echo "wh_a";
                            } ?>&from_dropdown=1" style="color:#000000;">Host</a>
                        <?php } else { ?>
                                Host
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_category'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                            ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                            echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php echo urlencode($new_end_date);
                            ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php echo urlencode($is_active);
                            ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                            if ($sort_by == "pc_a") {
                                echo "pc_d";
                            } else {
                                echo "pc_a";
                            } ?>&from_dropdown=1" style="color:#000000;">Category</a>
                        <?php } else { ?>
                                Category
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_domain_owner'] == "1") { ?>
                    <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                        <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>
                            <a href="index.php?pcid=<?php echo $pcid; ?>&oid=<?php echo $oid; ?>&dnsid=<?php echo $dnsid;
                            ?>&ipid=<?php echo $ipid; ?>&whid=<?php echo $whid; ?>&rid=<?php echo $rid; ?>&raid=<?php
                            echo $raid; ?>&start_date=<?php echo urlencode($new_start_date); ?>&end_date=<?php echo urlencode($new_end_date);
                            ?>&segid=<?php echo $segid; ?>&tld=<?php echo urlencode($tld); ?>&is_active=<?php echo urlencode($is_active);
                            ?>&result_limit=<?php echo urlencode($result_limit); ?>&sort_by=<?php
                            if ($sort_by == "o_a") {
                                echo "o_d";
                            } else {
                                echo "o_a";
                            } ?>&from_dropdown=1" style="color:#000000;">Owner</a>
                        <?php } else { ?>
                                Owner
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($_SESSION['s_cdf_data']) {

                    foreach ($_SESSION['s_cdf_data'] as $field) {

                        if ($field['value'] === 1 && $field['type_id'] != '3') { // Don't show column for Text Areas ?>

                            <th<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>

                                <?php if ($_SESSION['s_system_large_mode'] == '1') { ?>

                                    <span style="color:#000000;"><?php echo $field['name']; ?></span>

                                <?php } else { ?>

                                    <?php echo $field['name']; ?>

                                <?php } ?>

                            </th><?php

                        }

                    }

                } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($result as $row) { ?>
                <tr>

                    <?php if ($_SESSION['s_system_large_mode'] != '1') { ?>
                        <td></td>
                    <?php } ?>

                    <td>
                        <?php if ($row->active == "0") {
                            echo "<a title=\"Inactive Domain\"><strong>x</strong></a>&nbsp;";
                        } elseif ($row->active == "2") {
                            echo "<a title=\"Pending (Transfer)\"><strong>T</strong></a>&nbsp;";
                        } elseif ($row->active == "3") {
                            echo "<a title=\"Pending (Renewal)\"><strong>PRn</strong></a>&nbsp;";
                        } elseif ($row->active == "4") {
                            echo "<a title=\"Pending (Other)\"><strong>PO</strong></a>&nbsp;";
                        } elseif ($row->active == "5") {
                            echo "<a title=\"Pending (Registration)\"><strong>PRg</strong></a>&nbsp;";
                        } elseif ($row->active == "10") {
                            echo "<a title=\"Sold\"><strong>S</strong></a>&nbsp;";
                        }
                        ?>
                        <a href="edit.php?did=<?php echo $row->id; ?>"><?php echo $row->domain; ?></a><?php if ($row->privacy == "1") {
                            echo "&nbsp;<a title=\"Private WHOIS Registration\"><strong>prv</strong></a>";
                        } ?>
                    </td>
                    <?php if ($_SESSION['s_display_domain_expiry_date'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="edit.php?did=<?php echo $row->id; ?>"><?php echo $row->expiry_date; ?></a>
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_fee'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/registrar-fee.php?rid=<?php echo $row->r_id; ?>&fee_id=<?php echo $row->f_id; ?>">
                                <?php
                                $converted_total_cost = $row->total_cost * $row->conversion;
                                $temp_output_amount = $currency->format($converted_total_cost,
                                    $_SESSION['s_default_currency_symbol'], $_SESSION['s_default_currency_symbol_order'],
                                    $_SESSION['s_default_currency_symbol_space']);
                                echo htmlentities($temp_output_amount, ENT_QUOTES, 'UTF-8');
                                ?>
                            </a>
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_tld'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/registrar-fee.php?rid=<?php echo $row->r_id; ?>&fee_id=<?php echo $row->f_id; ?>">.<?php echo $row->tld; ?></a>
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_registrar'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/registrar.php?rid=<?php echo $row->r_id; ?>"><?php echo $row->registrar_name; ?></a>
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_account'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/registrar.php?rid=<?php echo $row->r_id; ?>"><?php echo $row->registrar_name; ?></a>,
                            <a href="../assets/edit/account-owner.php?oid=<?php echo $row->o_id; ?>"><?php echo $row->owner_name; ?></a>
                            (<a href="../assets/edit/registrar-account.php?raid=<?php echo $row->ra_id; ?>"><?php echo substr($row->username, 0, 15); ?><?php if (strlen($row->username) >= 16) echo "..."; ?></a>)
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_dns'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/dns.php?dnsid=<?php echo $row->dnsid; ?>"><?php echo $row->dns_name; ?></a>
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_ip'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/ip-address.php?ipid=<?php echo $row->ipid; ?>"><?php echo $row->ip_name; ?>
                                (<?php echo $row->ip; ?>)</a>
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_host'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/host.php?whid=<?php echo $row->whid; ?>"><?php echo $row->wh_name; ?></a>
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_category'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/category.php?pcid=<?php echo $row->pcid; ?>"><?php echo $row->category_name; ?></a>
                        </td>
                    <?php } ?>
                    <?php if ($_SESSION['s_display_domain_owner'] == "1") { ?>
                        <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>>
                            <a href="../assets/edit/account-owner.php?oid=<?php echo $row->o_id; ?>"><?php echo $row->owner_name; ?></a>
                        </td>
                    <?php } ?>

                    <?php if ($_SESSION['s_cdf_data']) {

                        foreach ($_SESSION['s_cdf_data'] as $field) {

                            if ($field['value'] === 1 && $field['type_id'] != '3') { // Don't show data for Text Areas ?>

                            <td<?php if ($_SESSION['s_system_large_mode'] == '1') { echo ' style="padding-left:20px;"'; } ?>><?php

                                if ($field['type_id'] === 1) { // Check Box

                                    echo ($row->{$field['field']} === 1 ? 'Yes' : 'No');

                                } elseif ($field['type_id'] === 2) { // Text

                                    echo $row->{$field['field']};

                                } elseif ($field['type_id'] === 4) { // Date

                                    if ($row->{$field['field']} == '1978-01-23') {

                                        echo '';

                                    } else {

                                        echo $row->{$field['field']};

                                    }

                                } elseif ($field['type_id'] === 5) { // Time Stamp

                                    if ($row->{$field['field']} == '1978-01-23 00:00:00') {

                                        echo '';

                                    } else {

                                        echo $row->{$field['field']};

                                    }

                                } ?>

                            </td><?php

                            }

                        }

                    } ?>

                </tr>
            <?php } ?>
        </tbody>
        </table><BR><?php

    }

    if ($_SESSION['s_system_large_mode'] == '1') {

        require DIR_INC . '/layout/pagination-large-mode.inc.php';
    }

} else {

    if ($_SESSION['s_has_domain'] == '1' && $_SESSION['s_has_registrar'] == '1' && $_SESSION['s_has_registrar_account'] == '1') { ?>

        Your search returned zero results.<?php

    }

} ?>
<?php require_once DIR_INC . '/layout/footer.inc.php';  //@formatter:on ?>
<?php require_once DIR_INC . '/layout/date-range-picker-footer.inc.php'; ?>
</body>
</html>
