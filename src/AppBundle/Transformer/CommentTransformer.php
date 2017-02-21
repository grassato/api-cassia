<?php

namespace AppBundle\Transformer;

use AppBundle\Entity\Comment;
use BaseBundle\Transformer\Transformer;

class CommentTransformer extends Transformer
{
    /**
     * @var array
     */
    protected $availableIncludes = [
    ];

    /**
     * @var array
     */
    protected $defaultIncludes = [
    ];

    /**
     * @param Comment $comment
     *
     * @return array
     */
    public function transform(Comment $comment)
    {
        $data = [
            'comment' => $comment->getContent(),
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => $this->generateUrl('get_comment', ['id' => $comment->getId()]),
                ],
            ],
        ];

        return $data;
    }
}
