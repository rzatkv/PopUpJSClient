<?php

namespace WHMCS\Module\Addon\PopupJSClient;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Module\Addon\PopupJSClient\ModuleView as AnnouncementView;
use WHMCS\Module\Addon\PopupJSClient\Model as Announcement;
class ModuleController {
    public static function config(): array
    {
        return [
            'name' => 'PopUpJSClient',
            'description' => 'پاپ آپ های زیبا برای WHMCS',
            'version' => '3.0',
            'author' => 'Reza Karimi',
            'author_email' => 'rzatkv@icloud.com',
            'fields' => []
        ];
    }

    public static function activate(): array
    {
        try {
            Capsule::schema()->create('rzatkv_popupjsclient_lists', function ($table) {
                $table->increments('id');
                $table->text('announcement');
                $table->timestamp('start_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->string('user_group')->nullable();
                $table->integer('views')->default(0);
                $table->boolean('is_multimedia')->default(false);
                $table->integer('priority')->default(3);
            });
        } catch (Exception $e) {
            return ['status' => 'error', 'description' => 'Unable to create table: ' . $e->getMessage()];
        }
        return ['status' => 'success', 'description' => 'Module activated successfully.'];
    }

    public static function deactivate(): array
    {
        try {
            Capsule::schema()->dropIfExists('rzatkv_popupjsclient_lists');
        } catch (Exception $e) {
            return ['status' => 'error', 'description' => 'Unable to drop table: ' . $e->getMessage()];
        }
        return ['status' => 'success', 'description' => 'Module deactivated successfully.'];
    }

    /**
     * @throws Exception
     */
    public static function manage($vars) {
        $lang = self::getLang($vars);

        if (isset($_POST['new_announcement'])) {
            $data = [
                'announcement' => $_POST['announcement'],
                'start_at' => $_POST['start_at'] ?? null,
                'expires_at' => $_POST['expires_at'] ?? null,
                'user_group' => $_POST['user_group'] ?? null,
                'is_multimedia' => isset($_POST['is_multimedia']) ? 1 : 0,
                'priority' => $_POST['priority'] ?? 3
            ];
            if (!empty($data['announcement'])) {
                try {
                    Announcement::add($data);
                    echo '<div class="successbox">' . $lang['add_announcement_success'] . '</div>';
                } catch (Exception $e) {
                    echo '<div class="errorbox">' . $lang['add_announcement_error'] . $e->getMessage() . '</div>';
                }
            }
        }

        if (isset($_POST['edit_announcement'])) {
            $announcement_id = $_POST['announcement_id'];
            $data = [
                'announcement' => $_POST['announcement'],
                'start_at' => $_POST['start_at'] ?? null,
                'expires_at' => $_POST['expires_at'] ?? null,
                'user_group' => $_POST['user_group'] ?? null,
                'is_multimedia' => isset($_POST['is_multimedia']) ? 1 : 0,
                'priority' => $_POST['priority'] ?? 3
            ];
            if (!empty($data['announcement'])) {
                try {
                    Announcement::update($announcement_id, $data);
                    echo '<div class="successbox">' . $lang['update_announcement_success'] . '</div>';
                } catch (Exception $e) {
                    echo '<div class="errorbox">' . $lang['update_announcement_error'] . $e->getMessage() . '</div>';
                }
            }
        }

        if (isset($_POST['delete_announcement'])) {
            $announcement_id = $_POST['announcement_id'];
            try {
                Announcement::delete($announcement_id);
                echo '<div class="successbox">' . $lang['delete_announcement_success'] . '</div>';
            } catch (Exception $e) {
                echo '<div class="errorbox">' . $lang['delete_announcement_error'] . $e->getMessage() . '</div>';
            }
        }

        AnnouncementView::renderForm($lang, $_POST);
        $announcements = Announcement::getAll();
        AnnouncementView::renderAnnouncementsList($lang, $announcements);
    }

    public static function getLang($vars) {
        $language = $vars['language'] ?? 'en';
        $langFile = __DIR__ . '/../resources/lang/' . $language . '.php';

        if (file_exists($langFile)) {
            return include $langFile;
        }

        return include __DIR__ . '/../resources/lang/en.php';
    }
}