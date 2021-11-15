<?php

namespace App;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterService {
    private $twitter;

    public function __construct(TwitterOAuth $twitter) {
        $this->twitter = $twitter;
    }

    public function searchTweets(string $query) {
        $this->twitter->setApiVersion('2');

        return $this->twitter->get("tweets/search/recent", [
            'query' => $query,
            'max_results' => 100,
            'expansions' => 'author_id'
        ])->data;
    }

    public function follow(int $twitterId) {
        return $this->twitter->post("friendships/create", ['user_id' => $twitterId]);
    }

    public function unfollow(int $twitterId) {
        return $this->twitter->post("friendships/destroy", ['user_id' => $twitterId]);
    }

    public function getFollowers(string $screenName) {
        return $this->twitter->get("followers/ids", [
            'screen_name' => $screenName, 'stringify_ids' => true
        ])->ids;
    }
}