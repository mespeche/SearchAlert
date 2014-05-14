<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia                                                                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace SearchAlert\Controller\Front;

use Propel\Runtime\Exception\PropelException;
use SearchAlert\EventListeners\SearchAlert;
use SearchAlert\Form\SearchAlertForm;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\ConfigQuery;

/**
 * Class SearchAlertController
 * @package SearchAlert\Controller\Front
 * @author Michaël Espeche <mespeche@openstudio.fr>
 */
class SearchAlertController extends BaseFrontController {


    public function send() {

        $alertForm = new SearchAlertForm($this->getRequest());

        $message = false;

        try {

            $form = $this->validateForm($alertForm);

            $event = $this->createEventInstance($form->getData());

            $this->dispatch(SearchAlert::SEARCH_ALERT_CREATE, $event);

            $body = Translator::getInstance()->trans('Congratulations,', array(), 'searchalert') . "\n\r";
            $body .= Translator::getInstance()->trans('Your alert has been created according to the search criteria you requested. You will be notified by email when a property matching your search will be online.', array(), 'searchalert') . "\n\r";

            $message = \Swift_Message::newInstance(Translator::getInstance()->trans('Your alert has been created.', array(), 'searchalert'))
                ->addFrom(ConfigQuery::read('store_email'), ConfigQuery::read('store_name'))
                ->addTo($form->get('email')->getData())
                ->setBody($body)
            ;

            $this->getMailer()->send($message);

            $this->redirectSuccess($alertForm);

        } catch (FormValidationException $e) {
            $message = sprintf("Please check your input: %s", $e->getMessage());
        } catch (PropelException $e) {
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $message = sprintf("Sorry, an error occured: %s", $e->getMessage()." ".$e->getFile());
        }

        if ($message !== false) {
            \Thelia\Log\Tlog::getInstance()->error(sprintf("Error during search alert creation process : %s.", $message));

            $alertForm->setErrorMessage($message);

            $this->getParserContext()
                ->addForm($alertForm)
                ->setGeneralError($message)
            ;

            return $this->render('category', array('category_id' => 1));
        }

    }

    /**
     * @param $data
     * @return \SearchAlert\EventListeners\SearchAlert
     */
    private function createEventInstance($data)
    {

        $alertCreationEvent = new SearchAlert($data['email'], $data['search']);

        return $alertCreationEvent;
    }

} 