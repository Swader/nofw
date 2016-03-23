<?php

namespace Standard\Controllers\Cron;

use Cake\ORM\TableRegistry;
use Psecio\Gatekeeper\UserModel;
use Standard\Abstracts\Controller;
use Twig_Environment;

/**
 * Class UsersController
 * @auth-groups admin
 * @package Standard\Controllers
 */
class CronController extends Controller
{

    /**
     * @var Twig_Environment
     */
    private $twig;

    /** @var \Cake\ORM\Table */
    private $cronSettings;

    /** @var \Cake\ORM\Table */
    private $cronsTable;

    /**
     * @Inject("User")
     * @var UserModel|null
     */
    private $user;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
        $this->cronSettings = TableRegistry::get('CronSettings');
        $this->cronsTable = TableRegistry::get('Cron');
    }

    public function listCrons()
    {
        echo $this->twig->render(
            'cron/list.twig', [
                'crons' => $this->cronsTable->find()
                    ->hydrate(false)
                    ->toArray(),
                'cronSettings' => $this->cronSettings->find()
                    ->hydrate(false)
                    ->toArray()[0],
            ]
        );
    }

    public function upsertCronGet(int $id = null)
    {
        $cron = ($id) ? $this->cronsTable->get(
            $id
        ) : $this->cronsTable->newEntity();

        echo $this->twig->render('cron/upsert.twig', ['cron' => $cron]);
    }

    public function upsertCronPost()
    {
        $id = $_POST['id'] ?? null;
        unset($_POST['id']);

        $cron = ($id)
            ? $this->cronsTable->get($id)->set($_POST)
            : $this->cronsTable->newEntity($_POST);

        if ($cron->dirty()) {
            try {
                $this->cronsTable->save($cron);
                $this->flasher->success("Success!");
            } catch (\Exception $e) {
                $this->flasher->error(
                    'Something went wrong while ' . (($cron->id) ? 'updating' : 'creating') . ' the cron - ' . $e->getMessage(
                    )
                );
            }
        }

        $this->redirect('/admin/crons');
    }

    public function saveSettings()
    {
        try {
            $this->cronSettings->save($this->cronSettings->get(1)->set($_POST));
            $this->flasher->success('Saved general cron settings!');
        } catch (\Exception $e) {
            $this->flasher->error(
                "Could not save general cron settings: " . $e->getMessage()
            );
        }
        $this->redirect('/admin/crons');
    }

    public function deleteCron()
    {
        $id = $_POST['id'] ?? null;
        if ($id) {
            try {
                $cron = $this->cronsTable->get($id);
                if (($_POST['deleteLog'] ?? false) == $id && $cron->output) {
                    $logFile = $this->site['logFolder'] . '/' . $cron->output;
                    if (!unlink($logFile)) {
                        $this->flasher->error(
                            "Could not delete log file: " . $logFile
                        );
                    }
                }
                $this->cronsTable->delete($cron);
                $this->flasher->success('Successfully deleted cron!');
            } catch (\Exception $e) {
                $this->flasher->error(
                    "Could not delete cron: " . $e->getMessage()
                );
            }
            $this->redirect('/admin/crons');
        }
    }
}
