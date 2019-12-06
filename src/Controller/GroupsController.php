<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Groups\Controller;

use Groups\Controller\AppController;

/**
 * Groups Controller
 *
 * @property \Groups\Model\Table\GroupsTable $Groups
 */
class GroupsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
        $this->set('groups', $this->paginate($this->Groups, [
            'contain' => [
                'Users' => function ($q) {
                    return $q->select(['Users.id', 'Users.username'])
                        ->order(['Users.username' => 'ASC']);
                }
            ],
            'maxLimit' => 500,
            'limit' => 500
        ]));
        $this->set('_serialize', ['groups']);
    }

    /**
     * View method
     *
     * @param string $id Group id.
     * @return \Cake\Http\Response|void|null
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function view(string $id)
    {
        $group = $this->Groups->get($id, [
            'contain' => ['Users' => function ($q) {
                return $q->select(['Users.id', 'Users.username', 'Users.first_name', 'Users.last_name']);
            }]
        ]);

        $this->set('group', $group);
        $this->set('_serialize', ['group']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|void|null Redirects on successful add, renders view otherwise.
     * */
    public function add()
    {
        $group = $this->Groups->newEntity();
        if ($this->request->is('post')) {
            /**
             * @var array $data
             */
            $data = $this->request->getData();
            $group = $this->Groups->patchEntity($group, $data);
            if ($this->Groups->save($group)) {
                $this->Flash->success((string)__d('Groups', 'The group has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error((string)__d('Groups', 'The group could not be saved. Please, try again.'));
            }
        }
        $users = $this->Groups->Users->find('list', ['limit' => 500]);
        $remoteGroups = $this->Groups->getRemoteGroups();
        $this->set(compact('group', 'users', 'remoteGroups'));
        $this->set('_serialize', ['group']);
    }

    /**
     * Edit method
     *
     * @param string $id Group id.
     * @return \Cake\Http\Response|void|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit(string $id)
    {
        $group = $this->Groups->get($id, [
            'contain' => ['Users']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            /**
             * @var array $data
             */
            $data = $this->request->getData();
            $group = $this->Groups->patchEntity($group, $data);
            if ($this->Groups->save($group)) {
                $this->Flash->success((string)__d('Groups', 'The group has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error((string)__d('Groups', 'The group could not be saved. Please, try again.'));
            }
        }
        $users = $this->Groups->Users->find('list', ['limit' => 500]);
        $remoteGroups = $this->Groups->getRemoteGroups();
        $this->set(compact('group', 'users', 'remoteGroups'));
        $this->set('_serialize', ['group']);
    }

    /**
     * Delete method
     *
     * @param string $id Group id.
     * @return \Cake\Http\Response|void|null Redirects to index.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function delete(string $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $group = $this->Groups->get($id);
        if ($this->Groups->delete($group)) {
            $this->Flash->success((string)__d('Groups', 'The group has been deleted.'));
        } else {
            $this->Flash->error((string)__d('Groups', 'The group could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
