<?php


namespace App\Presenters;


use Nette;

class HomePresenter extends Nette\Application\UI\Presenter
{
    public function renderDefault(): void {
        $this->template->csob = 'Im there';
    }
}