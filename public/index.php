<?php

/**
 * Developer Note:
 * I'm not going to write a controller and router for this tool
 * It's too simple and it is a single page app. Deal with it.
 */

require_once('../bootstrap.php');

$error = false;
$maxfilesize = ini_get("upload_max_filesize");

/**
 * Check File Upload
 * @param bool $error
 * @return bool
 */
function checkFile(&$error)
{
    if (isset($_FILES['file'])) {
        if ($_FILES['file']['error']) {
            $error = true;
            return false;
        }

        if ($_FILES['file']['type'] !== "application/json") {
            $error = true;
            return false;
        }

        return true;
    }

    return false;
}

/**
 * Convert File
 * @return void
 */
function convertFile($tempFile)
{
    $json = file_get_contents($tempFile);
    $player = \PBR20\Factory\Player::create($json);
    $struct = \PBR20\Services\Conversion::getInstance()->convert($player);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $player->name)));
    $filename = $slug.'-'.md5(uniqid('roll20', true)).'.json';

    header("Content-Disposition: attachment; filename=$filename;");
    header("Content-Type: application/json");

    echo json_encode($struct, JSON_PRETTY_PRINT);
    exit();
}

// Check File
if (checkFile($error)) {
    try {
        convertFile($_FILES['file']['tmp_name']);
    } catch (Exception $e) {
        $error = true;
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Pathbuilder 2e to Roll20 Conversion Tool</title>
    <meta name="description" content="An open-source tool that helps you convert your pathbuilder json files to be used in roll20."/>
    <link rel="stylesheet" href="resources/index.css"/>
</head>
<body>
    <div class='blur'></div>
    <div class='wrapper'>

        <h1>Pathbuilder 2e to Roll20 Conversion Tool</h1>
        <?php if ($error): ?>
            <div class='error-message'><strong>Error:</strong> There was a problem converting your file. Please check your pathbuilder JSON file and try again.</div>
        <?php endif; ?>

        <form method='POST' enctype='multipart/form-data'>
            <div>
                <label for='file'>File Upload</label>
                <em>JSON Files Only; Max File Size Limit: <?= $maxfilesize ?></em>
                <input type='file' id='file' name='file' accept='.json,application/json'/>
            </div>
            <div>
                <button type='submit'>Convert File</button>
            </div>
        </form>

        <div class='content'>
            <h2>How To Use</h2>
            <p>
                <strong>
                    WARNING: I AM NOT RESPONSIBLE FOR ANYTHING RELATED TO USING THIS TOOL. YOU ARE USING THIS TOOL AT YOUR OWN RISK.
                </strong>
            </p>

            <ul>
                <li>Login to <a href='https://pathbuilder2e.com/' target='_blank' rel='nofollow'>https://pathbuilder2e.com/</a> and download a JSON export of your character. (You must be a paid user).</li>
                <li>Upload file here and download your Roll20 compatible JSON file.</li>
                <li>Install and enable the <strong>VTT Enhancement Suite</strong>: <a href='https://justas-d.github.io/roll20-enhancement-suite/' target='_blank' rel='nofollow'>https://justas-d.github.io/roll20-enhancement-suite/</a> (Follow Directions on Site)</li>
                <li>Login to Roll 20 and go to your character sheet.</li>
                <li>At the top you will see <strong>Export &amp; Overwrite</strong> tab.</li>
                <li>In that tab you will see an <strong>overwrite</strong> button. Click that button and upload the JSON you got here.
                    <ul>
                        <li><strong>Warning:</strong> You will overwrite and lose all of your character data as a result.</li>
                        <li><strong>Note:</strong> The VTT import tool is jank, your browser may hang, just close it out completely and re-open it.</li>
                        <li><strong>Caution:</strong> If your browser continues to crash on Roll20 disable/remove the VTT after import.</li>
                    </ul>
                </li>
                <li>Go back to your character sheet and everything (mostly) should have been imported.</li>
                <li>For safety, you should go up and down a level to force a recalculation of all stats just to be safe. Check your AC and some other items, they may have been lost as a result of the re-calculation.</li>
            </ul>

            <h2>Known Limitations</h2>
            <ul>
                <li>Even though Max HP is being set it still needs a level up/down to recalculate.</li>
                <li>Weapons/Armor/Inventory/Gold do not port over on purpose.</li>
                <li>Because Pathbuilder does some non-standard things, some spells might not have any details.</li>
                <li>Example: Fiend's Door (Feat) is listed as a Feat and Innate spell, but it's actually just Dimension Door.</li>
                <li>There are some missing feats descriptions despite the large database.</li>
                <li>No spell attack calculations are done (you must do this yourself if you even use them).</li>
            </ul>

            <h2>Legal</h2>
            <p>
                This is an unoffical tool and not sponsored or endorsed by Paizo, Roll20, Pathbuilder, whoever. This is a free and open-soruce tool that has no warranty. For information on Paizo / Pathfinder licenses please see:
            </p>

            <ul>
                <li><a href='https://paizo.com/community/communityuse' target='_blank' rel='nofllow'>https://paizo.com/community/communityuse</a></li>
                <li><a href='https://paizo.com/pathfinder/compatibility' target='_blank' rel='nofllow'>https://paizo.com/pathfinder/compatibility</a></li>
            </ul>

            <p>
                To access the source code for this application please visit: <a href='https://github.com/KernelZechs/pathbuilder-roll20-conversion-tool' target='_blank'>https://github.com/KernelZechs/pathbuilder-roll20-conversion-tool</a>
            </p>
        </div>
    </div>
</body>
</html>
