<?php

namespace AppBundle\Transformer;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use BaseBundle\Transformer\Transformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostTransformer extends Transformer
{
    /**
     * @var array
     */
    protected $availableIncludes = [
        'comment',
    ];

    /**
     * @var array
     */
    protected $defaultIncludes = [
    ];

    /**
     * @param Post $post
     *
     * @return array
     */
    public function transform(Post $post)
    {
        $data = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => $this->generateUrl('get_post', ['id' => $post->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                ],
            ],
        ];

        return $data;
    }

    protected $commentTransformer;
    /**
     * Include Author.
     *
     * @return League\Fractal\ItemResource
     */
    public function includeComment(Post $post)
    {
        if ($post->getComments()->count() >  0) {
            return $this->collection($post->getComments(), $this->commentTransformer);
        }
    }

    public function setCommentTransformer(CommentTransformer $transformer)
    {
        $this->commentTransformer = $transformer;
    }
}
