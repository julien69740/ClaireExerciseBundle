<?php
/*
 * This file is part of CLAIRE.
 *
 * CLAIRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CLAIRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CLAIRE. If not, see <http://www.gnu.org/licenses/>
 */

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\TestExercise;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\ResourceBlock;

/**
 * Block of questions in a short answer question exercise model
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class QuestionBlock extends ResourceBlock
{
    /**
     * QuestionBlock constructor
     *
     * @param int $numberOfOccurrences
     *
     * @return QuestionBlock
     */
    function __construct(
        $numberOfOccurrences
    )
    {
        $this->numberOfOccurrences = $numberOfOccurrences;
    }
}
