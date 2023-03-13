<?php

namespace Enums;

// Defines the allowed parameters for each endpoint, and the allowed sort options


enum OrderSort
{
	case ASC;
	case DESC;
	case asc;
	case desc;
}

enum PostsFetchOrderValues
{
	case createdAt;
	case editedAt;
	case deletedAt;
	case id;
	case authorId;
	case autoArchiveAt;
	case bestMatch;
}

enum ViewsOrderValues
{
	case viewCount;
	case postId;
}

enum UsersFetchOrderValues
{
	case createdAt;
	case id;
	case email;
	case profilePictureId;
	case username;
}

enum TagsFetchOrderValues
{
	case id;
	case name;
	case description;
	case bestMatch;
}

enum CommentsFetchOrderValues
{
	case createdAt;
	case editedAt;
	case deletedAt;
	case id;
	case authorId;
	case postId;
}

class PostsFetchParams
{
	public const params = [
		'id' => null,
		'limit' => 100,
		'offset' => 0,
		'order' => ['createdAt' => 'DESC'],
		'fetchDeleted' => false,
		'showDeleted' => false,
		'ids' => null,
		'authorId' => null,
		'title' => null
	];

	public const default = ['limit', 'offset', 'order', 'showDeleted', 'fetchDeleted'];
}

class ViewsFetchParams
{
	public const params = [
		'postIds' => null,
		'order' => ['viewCount' => 'DESC'],
		'limit' => 100,
		'offset' => 0
	];

	public const default = ['limit', 'offset', 'order'];
}

class UsersFetchParams
{
	public const params = [
		'id' => null,
		'limit' => 100,
		'offset' => 0,
		'order' => ['createdAt' => 'DESC'],
		'ids' => null,
		'fetchDeleted' => false,
		'showDeleted' => false
	];

	public const default = ['limit', 'offset', 'order'];
}

class TagsFetchParams
{
	public const params = [
		'id' => null,
		'limit' => 100,
		'offset' => 0,
		'order' => ['id' => 'ASC'],
		'ids' => null,
		'name' => null
	];

	public const default = ['limit', 'offset', 'order'];
}

class PostTagsFetchParams
{
	public const params = [
		'limit' => 100,
		'offset' => 0,
		'ids' => null,
		'tags' => null
	];

	public const default = ['limit', 'offset', 'ids'];
}

class CommentsFetchParams
{
	public const params = [
		'id' => null,
		'limit' => 100,
		'offset' => 0,
		'order' => ['createdAt' => 'ASC'],
		'fetchDeleted' => false,
		'showDeleted' => false,
		'ids' => null,
		'postIds' => null,
	];

	public const default = ['limit', 'offset', 'order', 'showDeleted', 'fetchDeleted'];
}
