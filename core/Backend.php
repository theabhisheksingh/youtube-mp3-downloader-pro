<?php

namespace uniqueX;

class Backend
{
    public static function setValue($name, $value)
    {
        if (is_string($name)) {
            $GLOBALS[md5($name)] = $value;
        }
    }

    public static function getValue($name, $fallback = false)
    {
        $return = $fallback;
        if (is_string($name)) {
            $md5 = md5($name);
            if (isset($GLOBALS[$md5])) {
                $return = $GLOBALS[$md5];
            }
        }
        return $return;
    }

    public static function registerAdmin($category, $icon, $id, $name, $page)
    {
        if (is_string($category) && is_string($icon) && is_string($id) && is_string($name) && is_file($page)) {
            // Get admin pages
            $adminPages = self::getValue('adminPages');
            $adminPages = is_array($adminPages) ? $adminPages : array();
            // Register new
            $adminPages[] = array(
                'category' => $category,
                'icon' => $icon,
                'id' => $id,
                'name' => $name,
                'page' => $page
            );
            // Update admin pages
            self::setValue('adminPages', $adminPages);
        }
    }

    public static function registerPlugin($config)
    {
        if (is_array($config) && isset($config['id'])) {
            // Get plugins
            $plugins = self::getValue('plugins');
            $plugins = is_array($plugins) ? $plugins : array();
            // Register new plugin
            $plugins[$config['id']] = $config;
            // Update plugins
            self::setValue('plugins', $plugins);
        }
    }

    public static function checkPlugin($plugin)
    {
        $return = false;
        if (is_string($plugin)) {
            // Get all plugins
            $plugins = self::getValue('plugins');
            if (is_array($plugins) && isset($plugins[$plugin])) {
                $return = 0;
                // Get activated plugins
                $config = self::getValue('config');
                $active = is_array($config) && isset($config['plugins']) ? $config['plugins'] : array();
                // Search plugin on activated
                if (array_search($plugin, $active) !== false) {
                    $return = 1;
                }
            }
        }
        return $return;
    }

    public static function checkTemplate($template)
    {
        $return = false;
        if (is_string($template)) {
            // Get all templates
            $templates = self::getValue('templates');
            if (is_array($templates) && isset($templates[$template])) {
                // Get activated template
                $config = self::getValue('config');
                $active = is_array($config) && isset($config['template']) ? $config['template'] : null;
                // Match template
                $return = intval($template == $active);
            }
        }
        return $return;
    }

    public static function registerTemplate($config)
    {
        if (is_array($config) && isset($config['id'])) {
            // Get templates
            $templates = self::getValue('templates');
            $templates = is_array($templates) ? $templates : array();
            // Register new template
            $templates[$config['id']] = $config;
            // Update templates
            self::setValue('templates', $templates);
        }
    }

    public static function registerPage($id, $file)
    {
        // Get pages
        $pages = self::getValue('pages');
        $pages = is_array($pages) ? $pages : array();
        // Register new page
        $pages[$id] = $file;
        // Update pages
        self::setValue('pages', $pages);
    }

    public static function attachBody($content)
    {
        // Get attach body contents
        $attachBody = self::getValue('attachBody');
        $attachBody = is_array($attachBody) ? $attachBody : array();
        // Register new page
        $attachBody[] = $content;
        // Update pages
        self::setValue('attachBody', $attachBody);
    }

    public static function importFiles($file)
    {
        // Get import files
        $importFiles = self::getValue('importFiles');
        $importFiles = is_array($importFiles) ? $importFiles : array();
        // Register new file
        $importFiles[] = $file;
        // Update pages
        self::setValue('importFiles', $importFiles);
    }

    public static function activePlugin($id, $process)
    {
        // Get active plugins
        $activePlugins = self::getValue('activePlugins');
        $activePlugins = is_array($activePlugins) ? $activePlugins : array();
        // Active new plugin
        $activePlugins[] = $id;
        // Update pages
        self::setValue('activePlugins', $activePlugins);
        // Process plugin file
        self::importFiles($process);
    }

    public static function processPlugin($id)
    {
        // Get registered plugins
        $plugins = self::getValue('plugins');
        // Process plugin
        if (is_array($plugins) && isset($plugins[$id]) &&
            is_array($plugins[$id]) && isset($plugins[$id]['process']) &&
            is_file($plugins[$id]['process'])
        ) {
            self::activePlugin($id, $plugins[$id]['process']);
        }
    }

    public static function initTemplate()
    {
        // Get config
        $config = self::getValue('config');
        // Get available templates
        $templates = self::getValue('templates');
        // Get preview and original template
        $preview = isset($_COOKIE['templateID']) ? $_COOKIE['templateID'] : null;
        $original = is_array($config) && isset($config['template']) ? $config['template'] : null;
        // Start process
        if (is_array($templates) && count($templates) > 0) {
            // Final template
            if (isset($templates[$preview])) {
                $finalTemplate = $preview;
            } elseif (isset($templates[$original])) {
                $finalTemplate = $original;
            } else {
                $allTemplates = array_keys($templates);
                $finalTemplate = $allTemplates[0];
            }
            $templateData = $templates[$finalTemplate];
            // Process template
            self::setValue('template', $templateData);
            if (isset($templateData['process'])) {
                self::importFiles($templateData['process']);
            }
        }
    }

    public static function getFlavor()
    {
        $return = null;
        if (is_array($template = self::getValue('template'))) {
            // Get config
            $config = self::getValue('config');
            // Get preview and original flavors
            $flavor = isset($_COOKIE['templateFlavor']) ? $_COOKIE['templateFlavor'] : null;
            if ($flavor == null && is_array($config) && isset($config['template-flavor'])) {
                $flavor = $config['template-flavor'];
            }
            // Get random flavor
            if ($flavor == 'random' && isset($template['flavors']) &&
                is_array($template['flavors']) && count($template['flavors']) > 0
            ) {
                $flavorKeys = array_keys($template['flavors']);
                shuffle($flavorKeys);
                $flavor = $flavorKeys[0];
            }
            $return = $flavor;
        }
        return $return;
    }

    public static function getConfig($name = null)
    {
        $return = false;
        // Get config file path
        $configFile = __DIR__ . '/../store/' . self::installKey() . '.config';
        // Check config file
        if (is_file($configFile)) {
            // Decode config file data
            $configData = @unserialize(file_get_contents($configFile));
            if (is_array($configData)) {
                if ($name == null) {
                    // Dump total config
                    $return = $configData;
                } elseif (isset($configData[$name])) {
                    // Dump config value
                    $return = $configData[$name];
                }
            }
        }
        return $return;
    }

    public static function setConfig($data)
    {
        // Check data
        if (is_array($data) && count($data) > 0) {
            // Get config file path
            $configFile = __DIR__ . '/../store/' . self::installKey() . '.config';
            // Get existing config data
            $configData = self::getConfig();
            $configData = is_array($configData) ? $configData : array();
            // Update config data
            foreach ($data as $name => $value) {
                if (is_string($name)) {
                    $configData[$name] = $value;
                }
            }
            // Save config
            file_put_contents($configFile, serialize($configData));
        }
    }

    public static function importJavaScripts($files)
    {
        if (is_array($files)) {
            // Get javaScripts
            $javaScripts = self::getValue('javaScripts');
            $javaScripts = is_array($javaScripts) ? $javaScripts : array();
            // Register new javaScript
            foreach ($files as $file) {
                $javaScripts[] = $file;
            }
            // Update plugins
            self::setValue('javaScripts', $javaScripts);
        }
    }

    public static function importStyleSheets($files)
    {
        if (is_array($files)) {
            // Get styleSheets
            $styleSheets = self::getValue('styleSheets');
            $styleSheets = is_array($styleSheets) ? $styleSheets : array();
            // Register new styleSheet
            foreach ($files as $file) {
                $styleSheets[] = $file;
            }
            // Update plugins
            self::setValue('styleSheets', $styleSheets);
        }
    }

    public static function installKey()
    {
        $return = false;
        // Get install file
        $installFile = __DIR__ . '/../store/index.php';
        // Create install file if not exist
        if (!is_file($installFile)) {
            @file_put_contents($installFile, '<?php // ' . md5(microtime(true)));
        }
        // Get install file
        $installData = @file_get_contents($installFile);
        if (preg_match('/(?P<key>[a-f0-9]{32})/i', $installData, $matches)) {
            $return = $matches['key'];
        }
        return $return;
    }
}
