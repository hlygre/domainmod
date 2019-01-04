<?php
/**
 * /segments/index.php
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
<?php
require_once __DIR__ . '/../_includes/start-session.inc.php';
require_once __DIR__ . '/../_includes/init.inc.php';
require_once DIR_INC . '/config.inc.php';
require_once DIR_INC . '/software.inc.php';
require_once DIR_ROOT . '/vendor/autoload.php';

$deeb = DomainMOD\Database::getInstance();
$system = new DomainMOD\System();
$layout = new DomainMOD\Layout;
$time = new DomainMOD\Time();

require_once DIR_INC . '/head.inc.php';
require_once DIR_INC . '/debug.inc.php';
require_once DIR_INC . '/settings/segments-main.inc.php';

$system->authCheck();
$pdo = $deeb->cnxx;

$segid = (int) $_GET['segid'];
$export_data = $_GET['export_data'];

if ($export_data == "1") {

    if ($segid != "") {

        $seg_clause = " AND s.id = :segid ";

        $stmt = $pdo->prepare("
            SELECT `name`, number_of_domains
            FROM segments
            WHERE id = :segid");
        $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ($result) {

            $segment_name = $result->name;
            $number_of_domains = $result->number_of_domains;

        }

    } else {

        $seg_clause = "";

        $number_of_segments = $pdo->query("
            SELECT count(*)
            FROM segments")->fetchColumn();

        $number_of_segment_domains = $pdo->query("
            SELECT count(*)
            FROM segment_data")->fetchColumn();

    }

    if ($segid != "") {

        $base_filename = "segment";

    } else {

        $base_filename = "segment_list";

    }

    $export = new DomainMOD\Export();
    $export_file = $export->openFile($base_filename, strtotime($time->stamp()));

    if ($segid != "") {

        $row_contents = array(
            'Segment:',
            $segment_name
        );
        $export->writeRow($export_file, $row_contents);

        $row_contents = array(
            'Number of Domains in Segment:',
            $number_of_domains
        );
        $export->writeRow($export_file, $row_contents);

    } else {

        $row_contents = array($page_title);
        $export->writeRow($export_file, $row_contents);

        $export->writeBlankRow($export_file);

        $row_contents = array(
            'Total Number of Segments:',
            number_format($number_of_segments)
        );
        $export->writeRow($export_file, $row_contents);

        $row_contents = array(
            'Total Number of Domains:',
            number_format($number_of_segment_domains)
        );
        $export->writeRow($export_file, $row_contents);

    }

    $export->writeBlankRow($export_file);

    unset($row_contents);
    $count = 0;

    $row_contents[$count++] = "Segment";
    $row_contents[$count++] = "Description";
    $row_contents[$count++] = "Domain";
    if ($segid == "") {

        $row_contents[$count++] = "Number of Domains in Segment";

    }
    $row_contents[$count++] = "Creation Type";
    $row_contents[$count++] = "Created By";
    $row_contents[$count++] = "Insert Time";
    $row_contents[$count++] = "Update Time";
    $export->writeRow($export_file, $row_contents);

    // The only difference between this SELECT statement and the primary one below is that it uses a GROUP BY clause
    // The main one also doesn't check if $segid exists, since that's only needed when exporting an individual segment
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.description, s.segment, s.number_of_domains, s.notes, s.creation_type_id, s.created_by, s.insert_time, s.update_time, sd.domain
        FROM segments AS s, segment_data AS sd
        WHERE s.id = sd.segment_id" .
        $seg_clause . "
        ORDER BY s.name ASC, sd.domain ASC");
    if ($segid != '') {
        $stmt->bindValue('segid', $segid, PDO::PARAM_INT);
    }
    $stmt->execute();
    $result = $stmt->fetchAll();

    if ($result) {

        foreach ($result as $row) {

            $creation_type = $system->getCreationType($row->creation_type_id);

            if ($row->created_by == '0') {
                $created_by = 'Unknown';
            } else {
                $user = new DomainMOD\User();
                $created_by = $user->getFullName($row->created_by);
            }

            unset($row_contents);
            $count = 0;

            $row_contents[$count++] = $row->name;
            $row_contents[$count++] = $row->description;
            $row_contents[$count++] = $row->domain;
            if ($segid == "") {

                $row_contents[$count++] = $row->number_of_domains;
            }
            $row_contents[$count++] = $row->notes;
            $row_contents[$count++] = $creation_type;
            $row_contents[$count++] = $created_by;
            $row_contents[$count++] = $time->toUserTimezone($row->insert_time);
            $row_contents[$count++] = $time->toUserTimezone($row->update_time);
            $export->writeRow($export_file, $row_contents);

        }

    }

    $export->closeFile($export_file);

}
?>
<?php require_once DIR_INC . '/doctype.inc.php'; ?>
<html>
<head>
    <title><?php echo $layout->pageTitle($page_title); ?></title>
    <?php require_once DIR_INC . '/layout/head-tags.inc.php'; ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
<?php require_once DIR_INC . '/layout/header.inc.php'; ?>
Segments are lists of domains that can be used to help filter and manage your <a href="<?php echo $web_root; ?>/domains/">domain
    results</a>.<BR>
<BR>
Segment filters will tell you which domains match with domains that are saved in <?php echo SOFTWARE_TITLE; ?>, as well
as which domains don't match, and you can easily view and export the results.<BR>
<BR>
<a href="add.php"><?php echo $layout->showButton('button', 'Add Segment'); ?></a>
<a href="index.php?export_data=1"><?php echo $layout->showButton('button', 'Export'); ?></a><BR><BR><?php

$has_existing_segments = $pdo->query("
    SELECT id
    FROM segments
    LIMIT 1")->fetchColumn();

if (!$has_existing_segments) { ?>

    You don't currently have any Segments. <a href="add.php">Click here to add one</a>.<BR><BR><?php

} else { ?>

    <table id="<?php echo $slug; ?>" class="<?php echo $datatable_class; ?>">
        <thead>
        <tr>
            <th width="20px"></th>
            <th>Name</th>
            <th>Domains</th>
            <th>Count</th>
            <th>Export</th>
        </tr>
        </thead>
        <tbody><?php

        $result = $pdo->query("
            SELECT s.id, s.name, s.description, s.segment, s.number_of_domains, s.notes, s.creation_type_id,
                s.created_by, s.insert_time, s.update_time, sd.domain
            FROM segments AS s, segment_data AS sd
            WHERE s.id = sd.segment_id
            GROUP BY s.id
            ORDER BY s.name ASC, sd.domain ASC")->fetchAll();

        foreach ($result as $row) { ?>

            <tr>
                <td></td>
                <td>
                    <a href="edit.php?segid=<?php echo $row->id; ?>"><?php echo $row->name; ?></a>
                </td>
                <td><?php
                    $temp_segment = preg_replace("/','/", ", ", $row->segment);
                    $temp_segment = preg_replace("/'/", "", $temp_segment);
                    $segment = new DomainMOD\Segment();
                    $trimmed_segment = $segment->trimLength($temp_segment, 100); ?>
                    <?php echo $trimmed_segment; ?>
                </td>
                <td>
                    <?php echo $row->number_of_domains; ?>
                </td>
                <td>
                    <a href="index.php?export_data=1&segid=<?php echo $row->id; ?>">EXPORT</a>
                </td>
            </tr><?php

        } ?>

        </tbody>
    </table><?php

}
?>
<?php require_once DIR_INC . '/layout/footer.inc.php'; ?>
</body>
</html>
