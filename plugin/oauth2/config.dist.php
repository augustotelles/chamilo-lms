<?php
/**
 * Adjust user account values.
 *
 * @param array                          $response the Resource Owner Details
 * @param Chamilo\UserBundle\Entity\User $user     the user
 *                                                 with firstname, lastname, status, username and email already updated
 *                                                 but unsaved
 */
function oauth2_update_user_from_resource_owner_details(array $response, Chamilo\UserBundle\Entity\User $user)
{
    $user->setStatus(STUDENT);
    $user->setPhone($response['data'][0]['telephone']);
}
