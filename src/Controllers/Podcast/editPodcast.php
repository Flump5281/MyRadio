<?php
/**
 * Render form to create a new Podcast
 * @author Lloyd Wallis <lpw@ury.org.uk>
 * @author Andy Durant <aj@ury.org.uk>
 * @version 20140618
 * @package MyRadio_Podcast
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Submitted
    $data = MyRadio_Podcast::getForm()->readValues();

    if (empty($data['id'])) {
        $podcast = MyRadio_Podcast::create(
            $data['title'],
            $data['description'],
            explode(' ', $data['tags']),
            $data['file']['tmp_name'],
            empty($data['show']) ? null: MyRadio_Show::getInstance($data['show']),
            $data['credits']
        );
    } else {
        $podcast = MyRadio_Podcast::getInstance($data['id']);

        // Check if user can edit this podcast
        if (!in_array($podcast->getID(), MyRadio_Podcast::getPodcastIDsAttachedToUser())) {
            CoreUtils::requirePermission(AUTH_PODCASTANYSHOW);
        }

        $podcast->setMeta('title', $data['title'])
            ->setMeta('description', $data['description'])
            ->setMeta('tag', explode(' ', $data['tags']))
            ->setCredits($data['credits']['member'], $data['credits']['credittype']);

        if (!empty($data['show'])) {
            $podcast->setShow(MyRadio_Show::getInstance($data['show']));
        } else {
            $podcast->setShow(null);
        }
    }

    switch ($data['cover_method']) {
        case 'existing':
            $podcast->setCover($data['existing_cover']);
            break;
        case 'new':
            $podcast->createCover($data['new_cover']['tmp_name']);
            break;
        default:
            throw new MyRadioException('Unknown cover upload method.', 400);
    }

    CoreUtils::backWithMessage('Podcast Updated');

} else {
    //Not Submitted
    if (isset($_REQUEST['podcast_id'])) {
        MyRadio_Podcast::getInstance($_REQUEST['podcast_id'])
            ->getEditForm()
            ->render();

    } else {
        MyRadio_Podcast::getForm()->render();
    }
}
