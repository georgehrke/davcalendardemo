<?php
/**
 * @copyright 2020, Georg Ehrke <oc.list@georgehrke.com>
 *
 * @author Georg Ehrke <oc.list@georgehrke.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\DavCalendarDemo\DAV;

use OCA\DAV\CalDAV\Integration\ExternalCalendar;
use OCA\DAV\CalDAV\Plugin;
use Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet;
use Sabre\DAV\PropPatch;

class Calendar extends ExternalCalendar {

	/** @var string */
	private $principalUri;

	/** @var string */
	private $calendarUri;

	/** @var string[] */
	private $children;

	/**
	 * Calendar constructor.
	 *
	 * @param string $principalUri
	 * @param string $calendarUri
	 */
	public function __construct(string $principalUri, string $calendarUri) {
		parent::__construct('davcalendardemo', $calendarUri);

		$this->principalUri = $principalUri;
		$this->calendarUri = $calendarUri;

		$this->children = [
			'child-123.ics',
			'child-456.ics',
			'child-789.ics',
		];
	}


	/**
	 * @inheritDoc
	 */
	function getOwner() {
		return $this->principalUri;
	}

	/**
	 * @inheritDoc
	 */
	function getACL() {
		return [
			[
				'privilege' => '{DAV:}read',
				'principal' => $this->getOwner(),
				'protected' => true,
			],
			[
				'privilege' => '{DAV:}read',
				'principal' => $this->getOwner() . '/calendar-proxy-write',
				'protected' => true,
			],
			[
				'privilege' => '{DAV:}read',
				'principal' => $this->getOwner() . '/calendar-proxy-read',
				'protected' => true,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	function setACL(array $acl) {
		throw new \Sabre\DAV\Exception\Forbidden('Setting ACL is not supported on this node');
	}

	/**
	 * @inheritDoc
	 */
	function getSupportedPrivilegeSet() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	function calendarQuery(array $filters) {
		// In a real implementation this should actually filter
		return $this->children;
	}

	/**
	 * @inheritDoc
	 */
	function createFile($name, $data = null) {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	function getChild($name) {
		if ($this->childExists($name)) {
			return new CalendarObject($this, $name);
		}
	}

	/**
	 * @inheritDoc
	 */
	function getChildren() {
		$children = [];

		foreach ($this->children as $name) {
			$children[] = $this->getChild($name);
		}

		return $children;
	}

	/**
	 * @inheritDoc
	 */
	function childExists($name) {
		return \in_array($name, $this->children, true);
	}

	/**
	 * @inheritDoc
	 */
	function delete() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	function getLastModified() {
		return time();
	}

	/**
	 * @inheritDoc
	 */
	function getGroup() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	function propPatch(PropPatch $propPatch) {
		// We can just return here and let oc_properties handle everything
	}

	/**
	 * @inheritDoc
	 */
	function getProperties($properties) {
		// A backend should provide at least minimum properties
		return [
			'{DAV:}displayname' => 'Dav Example Calendar: ' . $this->calendarUri,
			'{http://apple.com/ns/ical/}calendar-color'  => '#565656',
			'{' . Plugin::NS_CALDAV . '}supported-calendar-component-set' => new SupportedCalendarComponentSet(['VTODO', 'VEVENT']),
		];
	}
}
