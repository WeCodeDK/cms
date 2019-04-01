<?php

namespace Statamic\Revisions;

use Statamic\API;
use Illuminate\Support\Carbon;
use Statamic\FluentlyGetsAndSets;
use Statamic\Data\ExistsAsFile;
use Statamic\API\Revision as Revisions;
use Statamic\Contracts\Auth\User;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\API\Arr;
use Statamic\Contracts\Revisions\Revision as Contract;

class Revision implements Contract, Arrayable
{
    use FluentlyGetsAndSets, ExistsAsFile;

    protected $id;
    protected $key;
    protected $date;
    protected $user;
    protected $userId;
    protected $message;
    protected $action = 'revision';
    protected $attributes = [];

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->value($id);
    }

    public function user($user = null)
    {
        if (is_null($user)) {
            if ($this->user) {
                return $this->user;
            }

            return $this->user = API\User::find($this->userId ?: null);
        }

        if ($user instanceof User) {
            $this->user = $user;
            $user = $user->id();
        }

        $this->userId = $user;

        return $this;
    }

    public function action($action = null)
    {
        return $this->fluentlyGetOrSet('action')->value($action);
    }

    public function message($message = null)
    {
        return $this->fluentlyGetOrSet('message')->value($message);
    }

    public function attributes($attributes = null)
    {
        return $this->fluentlyGetOrSet('attributes')->value($attributes);
    }

    public function attribute(string $key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function key($key = null)
    {
        return $this->fluentlyGetOrSet('key')->value($key);
    }

    public function date($date = null)
    {
        return $this->fluentlyGetOrSet('date')->value($date);
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.yaml', [
            Revisions::directory(),
            $this->key(),
            $this->date()->timestamp
        ]);
    }

    protected function fileData()
    {
        return [
            'action' => $this->action,
            'date' => $this->date->timestamp,
            'user' => $this->userId ?: null,
            'message' => $this->message ?: null,
            'attributes' => $this->attributes,
        ];
    }

    public function toArray()
    {
        return [
            'action' => $this->action,
            'date' => $this->date()->timestamp,
            'user' => Arr::only($this->user()->toArray(), ['id', 'email', 'name', 'avatar', 'initials']),
            'message' => $this->message,
            'attributes' => $this->attributes,
        ];
    }

    public function save()
    {
        Revisions::save($this);
    }

    public function delete()
    {
        Revisions::delete($this);
    }
}