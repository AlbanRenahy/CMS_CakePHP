<?php

namespace App\Controller;

class ArticlesController extends AppController
{
    /**
     * Display the list of articles 
     */
    public function index()
    {
        $this->loadComponent('Paginator');
        $articles = $this->Paginator->paginate($this->Articles->find());
        $this->set(compact('articles'));
    }

    /**
     * Display one specific article
     */
    public function view($slug = null)
    {
        $article = $this->Articles->findBySlug($slug)->contain('Tags')->firstOrFail();
        $this->set(compact('article'));
    }

    /**
     * Add an article
     */
    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            $article->user_id = $this->request->getAttribute('identity')->getIdentifier();

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Votre article a été sauvegardé.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Impossible d\'ajouter votre article.'));
        }
        // Récupère une liste des tags.
        $tags = $this->Articles->Tags->find('list');

        // Passe les tags au context de la view
        $this->set('tags', $tags);

        $this->set('article', $article);
    }

    /**
     * Edit an article
     */
    public function edit($slug)
    {
        $article = $this->Articles
            ->findBySlug($slug)
            ->contain('Tags')
            ->firstOrFail();

        if ($this->request->is(['post', 'put'])) {
            $this->Articles->patchEntity($article, $this->request->getData(), [
                'accessibleFields' => ['user_id' => false]
            ]);
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Votre article a bien été modifié.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Votre article n\'a pas pu être modifié.'));
        }
        // Récupère une liste des tags.
        $tags = $this->Articles->Tags->find('list');

        // Passe les tags au context de la view
        $this->set('tags', $tags);

        $this->set('article', $article);
    }

    /**
     * Delete an article
     */
    public function delete($slug)
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('L\'article a bien été supprimé.', $article->title));

            return $this->redirect(['action' => 'index']);
        }
    }

    public function tags()
    {
        // La clé 'pass' est fournie par CakePHP et contient tous les
        // segments d'URL passés dans la requête
        $tags = $this->request->getParam('pass');

        // Utilisation de ArticlesTable pour trouver les articles taggés
        $articles = $this->Articles->find('tagged', [
            'tags' => $tags
        ])
            ->all();

        // Passage des variables dans le contexte de la view du template
        $this->set([
            'articles' => $articles,
            'tags' => $tags
        ]);
    }
}
