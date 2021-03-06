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

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Domain\Version;

class ExpectationBuilder
{
    /** @var ConditionsBuilder */
    private $requestBuilder;

    public function __construct(ConditionsBuilder $requestBuilder)
    {
        $this->requestBuilder = $requestBuilder;
    }

    public function then(ResponseBuilder $responseBuilder): Expectation
    {
        return $this->createExpectation($responseBuilder->build());
    }

    public function thenRespond(int $statusCode, string $body): Expectation
    {
        $response = HttpResponseBuilder::create($statusCode)
            ->andBody($body)
            ->build();

        return $this->createExpectation($response);
    }

    private function createExpectation(Response $response): Expectation
    {
        $requestOptions = $this->requestBuilder->build();

        return new Expectation(
            $requestOptions->getRequestConditions(),
            $response,
            $requestOptions->getScenarioName(),
            null,
            new Version('2')
        );
    }
}
