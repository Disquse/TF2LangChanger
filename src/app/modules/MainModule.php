<?php
namespace app\modules;

use php\gui\framework\AbstractModule;
use php\gui\framework\ScriptEvent; 


class MainModule extends AbstractModule
{

    /**
     * @event TF2DirChooser.action 
     */
    function doTF2DirChooserAction(ScriptEvent $event = null)
    {    
        $this->form('MainForm')->hidePreloader();
    }

    /**
     * @event TF2DirChooser.cancel 
     */
    function doTF2DirChooserCancel(ScriptEvent $event = null)
    {    
        $this->form('MainForm')->hidePreloader();
    }


}
