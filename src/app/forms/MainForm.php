<?php
namespace app\forms;

use php\gui\UXDialog;
use php\gui\UXComboBox;
use php\io\File;
use php\gui\UXListView;
use php\lib\fs;
use action\Animation;
use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 
use php\gui\event\UXWindowEvent; 


class MainForm extends AbstractForm
{
    /**
     * @event showing 
     */
    function doShowing(UXWindowEvent $event = null)
    {    
        $this->ExitPanel->y = -57;
        if ( is_file('settings.ini') ) {
            $settings = $this->INISettings->toArray();
            $this->TF2DirectoryEdit->text = $settings['SETTINGS']['TF2Directory'];
            pre($settings);
        }    
    }
    
    /**
     * @event HeaderHelpButton.action 
     */
    function doHeaderHelpButtonAction(UXEvent $event = null)
    {
        UXDialog::showAndWait('Special thanks to Alexey Litvinov (http://vk.com/noobquire) Created by disquse.');
    }
    
    /**
     * @event HeaderExitButton.action 
     */
    function doHeaderExitButtonAction(UXEvent $event = null)
    {    
        $this->HeaderExitButton->enabled = false;
        $this->ExitPanel->enabled = false;
        Animation::moveTo($this->ExitPanel, 300, 0.0, 0.0, function () use ($event) {
            $this->HeaderExitButton->enabled = true;
            $this->ExitPanel->enabled = true;
        });
    }

    /**
     * @event ExitNoButton.action 
     */
    function doExitNoButtonAction(UXEvent $event = null)
    {    
        $this->HeaderExitButton->enabled = false;
        $this->ExitPanel->enabled = false;
        Animation::moveTo($this->ExitPanel, 300, 0.0, -57.0, function () use ($event) {
            $this->HeaderExitButton->enabled = true;
            $this->ExitPanel->enabled = true;
        });
    }

    /**
     * @event ExitYesButton.action 
     */
    function doExitYesButtonAction(UXEvent $event = null)
    {    
        Animation::fadeOut($this, 800, function () use ($event) {
            app()->shutdown();
        });
    }

    /**
     * @event TF2DirectoryBrowse.action 
     */
    function doTF2DirectoryBrowseAction(UXEvent $event = null)
    {    
        $this->showPreloader(' ');    
        $this->TF2DirChooser->execute();
    }

    /**
     * @event TF2DirectoryApply.action 
     */
    function doTF2DirectoryApplyAction(UXEvent $event = null)
    {    
        $tf2dir = $this->TF2DirectoryEdit->text;
        if ( is_dir($tf2dir) ) {
            if ( is_file("$tf2dir\\hl2.exe") and is_dir("$tf2dir\\tf") and is_dir("$tf2dir\\hl2") ) {
                fs::scan("$tf2dir\\hl2\\resource", function ($filename, $depth) {  
                    $foundedfile = File::of($filename)->getName();
                    if ( substr($foundedfile, 0, 4) == 'hl2_' and substr($foundedfile, -4) == '.txt') {
                        $this->FunctionsYourLang->items->add(substr($foundedfile, 4, -4));
                        $this->FunctionsSwitchingLang->items->add(substr($foundedfile, 4, -4));
                    }
                });
                if ( $this->FunctionsYourLang->items->count < 0 ) {
                    $this->toast("Can't found localization files!", 3000);
                } else {
                    $this->toast("Localization files was loaded!", 1000);
                    $this->FunctionsPanel->enabled = true;
                    $this->FunctionsPanel->opacity = 1.0; 
                    
                   $settings = $this->INISettings->set('TF2Directory', $this->TF2DirectoryEdit->text, 'SETTINGS');
                } 
            } else {
                $this->toast("Selected directory isn't have TF2 files!", 3000);
                $this->FunctionsPanel->enable = false;
                $this->FunctionsPanel->opacity = 0.5;
            }
        } else {
            $this->toast("Selected directory isn't valid!", 3000);
            $this->FunctionsPanel->enable = false;
            $this->FunctionsPanel->opacity = 0.5;
        }
    }

    /**
     * @event FunctionsApplyButton.action 
     */
    function doFunctionsApplyButtonAction(UXEvent $event = null)
    {    
        $switchingfiles = array(
            "\\hl2\\resource\\chat_",
            "\\hl2\\resource\\gameui_",
            "\\hl2\\resource\\hl2_",
            "\\hl2\\resource\\replay_",
            "\\hl2\\resource\\valve_",
            "\\hl2\\resource\\youtube_",
            "\\tf\\resource\\tf_",
            "\\tf\\resource\\tf_quests_",
        );
        foreach ( $switchingfiles as $prefix ) {
            $tf2dir = $this->TF2DirectoryEdit->text;
            $yourlanguage = $this->FunctionsYourLang->selected;
            $switchlanguage = $this->FunctionsSwitchingLang->selected;   
            
            if ( is_file($tf2dir.$prefix.$yourlanguage.'.txt') and is_file($tf2dir.$prefix.$yourlanguage.'.txt')) {
                $getyourlanguage = file_get_contents($tf2dir.$prefix.$yourlanguage.'.txt');
                $getswitchlanguage = file_get_contents($tf2dir.$prefix.$switchlanguage.'.txt'); 
                
                file_put_contents($tf2dir.$prefix.$yourlanguage.'.txt', $getswitchlanguage);
                file_put_contents($tf2dir.$prefix.$switchlanguage.'.txt', $getyourlanguage);
                $fail = 0;
            } else {
                $this->toast("One or few files can't be switched. Please, verify your game cache!", 5000);
                $fail = 1;
                break;
            }
        }
        if ( $fail != 1 ) {
            $this->toast("Success!", 1000);
        }
    }
}
