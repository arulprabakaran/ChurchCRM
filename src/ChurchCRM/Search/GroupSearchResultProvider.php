<?php

namespace ChurchCRM\Search;

use ChurchCRM\GroupQuery;

use Propel\Runtime\ActiveQuery\Criteria;
use ChurchCRM\Utils\LoggerUtils;
use ChurchCRM\Search\SearchResult;
use ChurchCRM\Search\SearchResultGroup;
use ChurchCRM\dto\SystemConfig;

class GroupSearchResultProvider implements iSearchResultProvider {

    public static function getSearchResults(string $SearchQuery) {
        if (SystemConfig::getBooleanValue("bSearchIncludeGroups")) {
            $searchResults = self::getPersonSearchResultsByPartialName($SearchQuery);
        }

        if (!empty($searchResults)) {
            return new SearchResultGroup(gettext('Groups'), $searchResults);
        }
        return null;
    }

    private static function getPersonSearchResultsByPartialName(string $SearchQuery) {
        $searchResults = array();
        $id = 0;
        try {
            $groups = GroupQuery::create()
                ->filterByName("%$SearchQuery%", Criteria::LIKE)
                ->limit(SystemConfig::getValue("bSearchIncludeGroupsMax"))
                ->find();
            if (!empty($groups)) {
                $id++;
                foreach ($groups as $group) {
                    array_push($searchResults, new SearchResult("group-name-".$id, $group->getName(),$group->getViewURI()));
                }
            }
        } catch (Exception $e) {
            LoggerUtils::getAppLogger()->warn($e->getMessage());
        }
        return $searchResults;
    }
}