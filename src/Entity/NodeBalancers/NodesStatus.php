<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Linode\Entity\NodeBalancers;

use Linode\Entity\Entity;

/**
 * A structure containing information about the health of the backends.
 *
 * @property int $up   The number of backends considered to be "UP" and healthy, and that
 *                     are serving requests.
 * @property int $down The number of backends considered to be "DOWN" and unhealthy. These
 *                     are not in rotation, and not serving requests.
 */
class NodesStatus extends Entity
{
}
