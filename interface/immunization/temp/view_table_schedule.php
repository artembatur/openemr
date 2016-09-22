<html>
<head>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <style type="text/css">
        .immunization_codes {
            border-left: 1px #000000 solid;
            border-right: 1px #000000 solid;
            border-top: 1px #000000 solid;
            width: 90%;
        }
        .immunization_codes thead tr {
            height: 24px;
            background: lightgrey;
        }
        .immunization_codes tbody tr {
            height: 24px;
            background:white;
        }
        .immunization_codes td, .immunization_codes th {
            border-bottom: 1px #000000 solid;
            border-right: 1px #000000 solid;
            padding: 10px;
        }
        .immunization_codes th {
            text-align: left;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>

</head>
<body class="body_top">
<div class="success">
    <?php
    if(isset($successMessage)) {
        echo $successMessage;
    }
    ?>
</div>
<div class="error">
    <?php
    if(isset($errorMessage)) {
        echo $errorMessage;
    }
    ?>
</div>
<span class="title"><?php xl('Immunization Schedules ','e');?></span>
<a class="more" href="/interface/immunization/schedules.php?action=add">Add new</a>
<br><br>
<table cellspacing="0" class="immunization_codes">
    <thead>
    <tr>
        <th>Description</th>
        <th>Age</th>
        <th>Age Max</th>
        <th>Frequency</th>
        <td></td>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach($rows as $row) {
        ?>
        <tr>
            <td><?=$row['description']?></td>
            <td><?=$row['age']?></td>
            <td><?=$row['age_max']?></td>
            <td><?=$row['frequency']?></td>
            <td><a href="/interface/immunization/schedules.php?action=edit&id=<?=$row['id']?>"">Edit</a>
                <a href="/interface/immunization/schedules.php?action=del&id=<?=$row['id']?>" onclick="return confirm('Are you sure you want to delete this schedule?') ? true : false">Del</a>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
</body>
</html>