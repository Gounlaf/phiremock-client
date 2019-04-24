<?php
/**
 * This file is part of Phiremock.
 *
 * Phiremock is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Phiremock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Phiremock.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mcustiel\Phiremock\Client\Utils;

use Mcustiel\Phiremock\Domain\Condition;
use Mcustiel\Phiremock\Domain\Conditions\BodyCondition;
use Mcustiel\Phiremock\Domain\Conditions\HeaderCondition;
use Mcustiel\Phiremock\Domain\Conditions\HeaderConditionCollection;
use Mcustiel\Phiremock\Domain\Conditions\Matcher;
use Mcustiel\Phiremock\Domain\Conditions\UrlCondition;
use Mcustiel\Phiremock\Domain\Http\BinaryBody;
use Mcustiel\Phiremock\Domain\Http\HeaderName;
use Mcustiel\Phiremock\Domain\Http\Method;
use Mcustiel\Phiremock\Domain\Http\Url;
use Mcustiel\Phiremock\Domain\Options\Priority;
use Mcustiel\Phiremock\Domain\Options\ScenarioName;
use Mcustiel\Phiremock\Domain\Options\ScenarioState;
use Mcustiel\Phiremock\Domain\RequestConditions;

class ConditionsBuilder
{
    /** @var Method */
    private $method;
    /** @var UrlCondition */
    private $urlCondition;
    /** @var BodyCondition */
    private $bodyCondition;
    /** @var HeaderConditionCollection */
    private $headers;
    /** @var ScenarioName */
    private $scenarioName;
    /** @var ScenarioState */
    private $scenarioIs;
    /** @var Priority */
    private $priority;

    public function __construct(Method $method, Url $url = null)
    {
        $this->headers = new HeaderConditionCollection();
        $this->method = $method;
        if (null !== $url) {
            $this->urlCondition = new UrlCondition(Matcher::equalTo(), $url->asString());
        }
    }

    /**
     * @param string      $method
     * @param null|string $url
     *
     * @return self
     */
    public static function create($method, $url = null)
    {
        return new static(new Method($method), null === $url ? null : new Url($url));
    }

    /**
     * @param Condition $condition
     *
     * @return self
     */
    public function andBody(Condition $condition)
    {
        $this->bodyCondition = BodyCondition::fromCondition($condition);

        return $this;
    }

    /**
     * @param Condition $condition
     *
     * @return self
     */
    public function andBinaryBody(Condition $condition)
    {
        $this->bodyCondition = new BodyCondition(
            $condition->getMatcher(),
            new BinaryBody($condition->getValue())
        );

        return $this;
    }

    /**
     * @param string    $header
     * @param Condition $condition
     *
     * @return self
     */
    public function andHeader($header, Condition $condition)
    {
        $this->headers->setHeaderCondition(
            new HeaderName($header),
            HeaderCondition::fromCondition($condition)
        );

        return $this;
    }

    /**
     * @param Condition $condition
     *
     * @return self
     */
    public function andUrl(Condition $condition)
    {
        $this->urlCondition = UrlCondition::fromCondition($condition);

        return $this;
    }

    /**
     * @param string $scenario
     * @param string $scenarioState
     *
     * @return self
     */
    public function andScenarioState($scenario, $scenarioState)
    {
        $this->scenarioName = new ScenarioName($scenario);
        $this->scenarioIs = new ScenarioState($scenarioState);

        return $this;
    }

    /**
     * @param int $priority
     *
     * @return self
     */
    public function andPriority($priority)
    {
        $this->priority = new Priority($priority);

        return $this;
    }

    /** @return ConditionsBuilderResult */
    public function build()
    {
        return new ConditionsBuilderResult(
            new RequestConditions(
                $this->method,
                $this->urlCondition,
                $this->bodyCondition,
                $this->headers
            ),
            $this->scenarioName,
            $this->priority
        );
    }
}