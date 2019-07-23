<?php
/**
 * settings.php
 *
 * Global settings, only accessible for root.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    ConverisProjects
 */

class SettingsController extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->plugin = $this->dispatcher->plugin;
            $this->flash = Trails_Flash::instance();

            if (Request::isXhr()) {
                $this->set_layout(null);
            } else {
                $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
            }

            $this->sidebar = Sidebar::get();
            $this->sidebar->setImage('sidebar/admin-sidebar.png');

            if (Studip\ENV == 'development') {
                $style = $this->plugin->getPluginURL().'/assets/stylesheets/converisplugin.css';
            } else {
                $css = $this->plugin->getPluginURL().'/assets/stylesheets/converisplugin.min.css';
            }
            PageLayout::addStylesheet($css);

        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Show all available templates for exports of research reports.
     */
    public function templates_action()
    {
        Navigation::activateItem('/tools/converisprojects/templates');
        PageLayout::setTitle(dgettext('converisplugin', 'Vorlagen für Forschungsberichte'));

        $this->templates = Config::get()->CONVERIS_REPORT_TEMPLATES;
    }

    /**
     * Save changes in template data.
     */
    public function save_templates_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        Config::get()->store('CONVERIS_REPORT_TEMPLATES', Request::getArray('templates'));

        PageLayout::postSuccess(dgettext('converisplusin', 'Die Berichtsvorlagen wurden gespeichert.'));

        $this->relocate('settings/templates');
    }

    /**
     * List all admins.
     */
    public function admins_action()
    {
        Navigation::activateItem('/tools/converisprojects/admins');
        PageLayout::setTitle(dgettext('converisplugin', 'Berechtigungen'));

        $this->admins = ConverisAdmin::findBySQL("1");
        usort($this->admins, function ($a, $b) {
            return strnatcasecmp(
                $a->user->nachname . '_' . $a->user->vorname . '_' . $a->user->username,
                $b->user->nachname . '_' . $b->user->vorname . '_' . $b->user->username
            );
        });

        $actions = new ActionsWidget();
        $actions->addLink(dgettext('converisplugin', 'Admin hinzufügen'),
            $this->url_for('settings/edit_admin'),
            Icon::create('add'))->asDialog('size=auto');
        $this->sidebar->addWidget($actions);
    }

    /**
     * Add or edit an admin and according permissions.
     *
     * @param string $admin_id the admin acccount to edit or add
     */
    public function edit_admin_action($admin_id = '')
    {
        Navigation::activateItem('/tools/converisprojects/admins');

        SimpleORMap::expireTableScheme();

        $title = $admin_id ?
            dgettext('converisplugin', 'Berechtigung bearbeiten') :
            dgettext('converisplugin', 'Berechtigung erteilen');

        PageLayout::setTitle($title);

        $this->admin = $admin_id != '' ? ConverisAdmin::find($admin_id) : new ConverisAdmin();
        $this->usersearch = QuickSearch::get('user_id', new StandardSearch('user_id'))
            ->withButton();
    }

    public function save_admin_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::option('admin_id') != '') {
            $admin = ConverisAdmin::find(Request::option('admin_id'));
        } else {
            $admin = new ConverisAdmin();
            $admin->user_id = Request::option('user_id');
            $admin->mkdate = date('Y-m-d H:i:s');
        }
        $admin->type = Request::option('type');
        $admin->chdate = date('Y-m-d H:i:s');

        if ($admin->store()) {
            PageLayout::postSuccess(dgettext('converisplugin', 'Die Einstellungen wurden gespeichert.'));
        } else {
            PageLayout::postError(dgettext('converisplugin', 'Die Einstellungen konnten nicht gespeichert werden.'));
        }

        $this->relocate('settings/admins');
    }

    public function delete_admin_action($admin_id)
    {
        $admin = ConverisAdmin::find($admin_id);
        if ($admin->delete()) {
            PageLayout::postSuccess(dgettext('converisplugin', 'Die Berechtigung wurde entfernt.'));
        } else {
            PageLayout::postError(dgettext('converisplugin', 'Die Berechtigung konnte nicht entfernt werden.'));
        }

        $this->relocate('settings/admins');
    }

    // customized #url_for for plugins
    public function url_for($to = '') {
        $args = func_get_args();

        # find params
        $params = [];
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map("urlencode", $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->plugin, $params, join("/", $args));
    }

}