<?php
echo "-------------------------------------------------------------------------------------\n";
echo " GENERATE AN EMPTY CsCart ADDON \n";
echo "-------------------------------------------------------------------------------------\n\n";

$settings = [
    'name' => ['Addon name', 'My CsCart Addon'],
    'description' => ['Addon description', 'A very nice plugin'],
    'directory' => ['Addon directory', 'my_cscart_addon'],
    'theme' => ['Theme directory', '[YOUR_THEME]'],
    'logo' => ['Logo file', '_source/icon.png'],
    'styles' => ['Use custom styles? (Y/N)', 'Y'],
    'scripts' => ['Use custom scripts? (Y/N)', 'Y'],
    'language_files' => ['Generate language files?', 'nl,de,en']
];

$configs = [];
foreach ($settings as $settingName => $settingValue) {
    $input = readline("{$settingValue[0]} [{$settingValue[1]}] ");
    $configs[$settingName] = !empty($input) ? $input : $settingValue[1];
}

$configs['styles'] = strtolower($configs['styles']) !== 'n';
$configs['scripts'] = strtolower($configs['scripts']) !== 'n';
$configs['language_files'] = explode(',', $configs['language_files']);

echo "\n-------------------------------------------------------------------------------------\n\n";
if (!file_exists($configs['logo'])) {
    die("ERROR! Logo file could not be found: '{$configs['logo']}'\n");
}

echo "GENERATING FILES...\r\n";

$dir = $configs['directory'];
$theme = $configs['theme'];

// Default directories
mkdir("design/themes/{$theme}/templates/addons/{$dir}/hooks/index", 0777, true);
mkdir("app/addons/{$dir}", 0777, true);

// Addon files
file_put_contents("app/addons/{$dir}/init.php", "<?php\nif (!defined('BOOTSTRAP')) { die('Access denied'); }\n");
file_put_contents("app/addons/{$dir}/func.php", "<?php\nif (!defined('BOOTSTRAP')) { die('Access denied'); }\n");

// Addon XML
$addonXml = strtr(
    file_get_contents('_source/addon.xml'), [
        '{directory}' => $dir,
        '{name}' => $configs['name']
    ]
);
file_put_contents("app/addons/{$dir}/addon.xml", $addonXml);

// Logo
mkdir("design/backend/media/images/addons/{$dir}", 0777, true);
copy($configs['logo'], "design/backend/media/images/addons/{$dir}/icon.png");

// Styles
if ($configs['styles']) {
    mkdir("design/themes/{$theme}/css/addons/{$dir}", 0777, true);
    touch("design/themes/{$theme}/css/addons/{$dir}/styles.less");
    file_put_contents(
        "design/themes/{$theme}/templates/addons/{$dir}/hooks/index/styles.post.tpl",
        "{style src=\"addons/{$dir}/styles.less\"}\n"
    );
}

// Scripts
if ($configs['scripts']) {
    mkdir("design/themes/{$theme}/css/addons/{$dir}", 0777, true);
    touch("design/themes/{$theme}/css/addons/{$dir}/scripts.js");
    file_put_contents(
        "design/themes/{$theme}/templates/addons/{$dir}/hooks/index/scripts.post.tpl",
        "{script src=\"addons/{$dir}/scripts.js\"}\n"
    );
}

// Language files
foreach ($configs['language_files'] as $lang) {
    mkdir("var/langs/{$lang}/addons", 0777, true);

    $languageFileContents = strtr(
        file_get_contents('_source/language.po'), [
            '{dir}' => $dir,
            '{langLow}' => strtolower($lang),
            '{langUp}' => strtoupper($lang),
            '{name}' => $configs['name'],
            '{description}' => $configs['description'],
            '{date}' => date('Y-m-d')
        ]
    );
    file_put_contents("var/langs/{$lang}/addons/{$dir}.po", $languageFileContents);
}

echo "Generated.\n";
echo "\n-------------------------------------------------------------------------------------\n\n";
