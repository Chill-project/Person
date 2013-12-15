<?php

namespace CL\Chill\PersonBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use CL\Chill\PersonBundle\Entity\Person;

class PersonToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (issue) to a string (id).
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($issue)
    {
        if (null === $issue) {
            return "";
        }

        return $issue->getId();
    }

    /**
     * Transforms a string (id) to an object (issue).
     *
     * @param  string $id
     *
     * @return Issue|null
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $issue = $this->om
            ->getRepository('CLChillPersonBundle:Person')
            ->findOneBy(array('id' => $id))
        ;

        if (null === $issue) {
            throw new TransformationFailedException(sprintf(
                'An issue with id "%s" does not exist!',
                $id
            ));
        }

        return $issue;
    }
}
?>