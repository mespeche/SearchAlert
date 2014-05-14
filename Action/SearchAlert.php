<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
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
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace SearchAlert\Action;

use SearchAlert\EventListeners\SearchAlert as SearchAlertEvent;
use SearchAlert\Model\Base\SearchAlertQuery;
use SearchAlert\Model\SearchAlert as SearchAlertModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * SearchAlert class where all actions are managed
 *
 * Class SearchAlert
 * @package SearchAlert\Action
 * @author Michaël Espeche <mespeche@openstudio.fr>
 */
class SearchAlert implements EventSubscriberInterface
{

    public function searchAlertCreation(SearchAlertEvent $event) {

        if(null === SearchAlertQuery::create()->findOneByArray(array('email' => $event->getEmail(), 'search' => $event->getSearch()))) {
            $alert = new SearchAlertModel();
            $alert->setEmail($event->getEmail())
                ->setSearch($event->getSearch())
                ->save();
        }
        else {
            throw new \Exception("This combination email/search already exists");
        }

    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            SearchAlertEvent::SEARCH_ALERT_CREATE => array('searchAlertCreation', 128)
        );
    }
}
