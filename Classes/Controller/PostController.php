<?php

namespace ExtbaseTeam\BlogExample\Controller;

use ExtbaseTeam\BlogExample\Domain\Model\Blog;
use ExtbaseTeam\BlogExample\Domain\Model\Comment;
use ExtbaseTeam\BlogExample\Domain\Model\Post;
use ExtbaseTeam\BlogExample\Domain\Repository\PersonRepository;
use ExtbaseTeam\BlogExample\Domain\Repository\PostRepository;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * The post controller for the BlogExample extension
 */
class PostController extends \ExtbaseTeam\BlogExample\Controller\AbstractController
{

    /**
     * @var \ExtbaseTeam\BlogExample\Domain\Repository\PostRepository
     */
    protected $postRepository;

    /**
     * @var \ExtbaseTeam\BlogExample\Domain\Repository\PersonRepository
     */
    protected $personRepository;

    /**
     * Dependency injection of the Post Repository
     *
     * @param \ExtbaseTeam\BlogExample\Domain\Repository\PostRepository $postRepository
     * @return void
     */
    public function injectPostRepository(PostRepository $postRepository): void
    {
        $this->postRepository = $postRepository;
    }

    /**
     * Dependency injection of the Post Repository
     *
     * @param \ExtbaseTeam\BlogExample\Domain\Repository\PersonRepository $personRepository
     * @return void
     */
    public function injectPersonRepository(PersonRepository $personRepository): void
    {
        $this->personRepository = $personRepository;
    }

    /**
     * Displays a list of posts. If $tag is set only posts matching this tag are shown
     *
     * @param \ExtbaseTeam\BlogExample\Domain\Model\Blog $blog The blog to show the posts of
     * @param string $tag The name of the tag to show the posts for
     * @return void
     */
    public function indexAction(Blog $blog, $tag = null)
    {
        if (empty($tag)) {
            $posts = $this->postRepository->findByBlog($blog);
        } else {
            $tag = urldecode($tag);
            $posts = $this->postRepository->findByTagAndBlog($tag, $blog);
            $this->view->assign('tag', $tag);
        }
        $this->view->assign('blog', $blog);
        $this->view->assign('posts', $posts);
    }

    /**
     * Displays one single post
     *
     * @param \ExtbaseTeam\BlogExample\Domain\Model\Post $post The post to display
     * @param \ExtbaseTeam\BlogExample\Domain\Model\Comment $newComment A new comment
     * @return void
     * @IgnoreValidation $newComment
     */
    public function showAction(Post $post, Comment $newComment = null)
    {
        $this->view->assign('post', $post);
        $this->view->assign('newComment', $newComment);
    }

    /**
     * Displays a form for creating a new post
     *
     * @param Blog $blog The blog the post belogs to
     * @param Post $newPost A fresh post object taken as a basis for the rendering
     * @return void
     * @IgnoreValidation $newPost
     */
    public function newAction(Blog $blog, Post $newPost = null)
    {
        $this->view->assign('authors', $this->personRepository->findAll());
        $this->view->assign('blog', $blog);
        $this->view->assign('newPost', $newPost);
        $this->view->assign('remainingPosts', $this->postRepository->findByBlog($blog));
    }

    /**
     * Creates a new post
     *
     * @param Blog $blog The blog the post belogns to
     * @param Post $newBlog A fresh Blog object which has not yet been added to the repository
     * @return void
     */
    public function createAction(Blog $blog, Post $newPost)
    {
        // TODO access protection
        $blog->addPost($newPost);
        $newPost->setBlog($blog);
        $this->addFlashMessage('created');
        $this->redirect('index', null, null, ['blog' => $blog]);
    }

    /**
     * Displays a form to edit an existing post
     *
     * @param Blog $blog The blog the post belogs to
     * @param Post $post The original post
     * @return void
     * @IgnoreValidation $post
     */
    public function editAction(Blog $blog, Post $post)
    {
        $this->view->assign('authors', $this->personRepository->findAll());
        $this->view->assign('blog', $blog);
        $this->view->assign('post', $post);
        $this->view->assign('remainingPosts', $this->postRepository->findRemaining($post));
    }

    /**
     * Updates an existing post
     *
     * @param Blog $blog The blog the post belongs to
     * @param Post $post A clone of the original post with the updated values already applied
     * @return void
     */
    public function updateAction(Blog $blog, Post $post): void
    {
        // TODO access protection
        $this->postRepository->update($post);
        $this->addFlashMessage('updated');
        $this->redirect('show', null, null, ['post' => $post, 'blog' => $blog]);
    }

    /**
     * Deletes an existing post
     *
     * @param Blog $blog The blog the post belongs to
     * @param Post $post The post to be deleted
     * @return void
     */
    public function deleteAction(Blog $blog, Post $post): void
    {
        // TODO access protection
        $this->postRepository->remove($post);
        $this->addFlashMessage('deleted', FlashMessage::INFO);
        $this->redirect('index', null, null, ['blog' => $blog]);
    }
}