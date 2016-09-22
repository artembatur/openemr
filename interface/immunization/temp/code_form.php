<html>
<head>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <style type="text/css">
        .immunization_codes td, .immunization_codes th {
            padding: 10px;
            border-bottom: 1px solid black;
        }
        .immunization_codes th {
            text-align: left;
        }
        .immunization_code_form input {
            width: 400px;
        }
        .error {
            color: red;
        }
    </style>

</head>
<body class="body_top">
<span class="title"><?php echo $action == 'add' ? 'Add' : 'Edit'; echo ' code';?></span>
<br><br>
<div class="error">
    <?php
    if(isset($errorMessage)) {
        echo $errorMessage;
    }
    ?>
</div>

<form method="post" action="/interface/immunization/codes.php">
    <table class="immunization_code_form">
        <tr>
            <td><label for="description">Description</label></td>
            <td><input type="text" name="immunization_code[description]" value="<?php if(isset($result['description'])) echo $result['description'] ?>"></td>
        </tr>
        <tr>
            <td><label for="cccc">Manufacturer</label></td>
            <td><input type="text" name="immunization_code[manufacturer]" value="<?php if(isset($result['manufacturer'])) echo $result['manufacturer'] ?>"></td>
        </tr>
        <tr>
            <td><label for="cccc">Cvx code</label></td>
            <td><input type="text" name="immunization_code[cvx_code]" value="<?php if(isset($result['cvx_code'])) echo $result['cvx_code'] ?>"></td>
        </tr>
        <tr>
            <td><label for="cccc">Proc codes</label></td>
            <td><input type="text" name="immunization_code[proc_codes]" value="<?php if(isset($result['proc_codes'])) echo $result['proc_codes'] ?>"></td>
        </tr>
        <tr>
            <td><label for="cccc">Justify codes</label></td>
            <td><input type="text" name="immunization_code[justify_codes]" value="<?php if(isset($result['justify_codes'])) echo $result['justify_codes'] ?>"></td>
        </tr>
        <tr>
            <td><label for="cccc">Default site</label></td>
            <td><input type="text" name="immunization_code[default_site]" value="<?php if(isset($result['default_site'])) echo $result['default_site'] ?>"></td>
        </tr>
        <tr>
            <td><label for="cccc">Comments</label></td>
            <td><input type="text" name="immunization_code[comments]" value="<?php if(isset($result['comments'])) echo $result['comments'] ?>"></td>
        </tr>
        <tr>
            <td><label for="cccc">Drug route</label></td>
            <td><input type="text" name="immunization_code[drug_route]" value="<?php if(isset($result['drug_route'])) echo $result['drug_route'] ?>"></td>
        </tr>
    </table>
    <input type="hidden" name="immunization_code[id]" value="<?php if(isset($result['id'])) echo $result['id'] ?>">
    <button><?php echo $action == 'add' ? 'Add' : 'Save';?></button>
</form>
</body>
</html>