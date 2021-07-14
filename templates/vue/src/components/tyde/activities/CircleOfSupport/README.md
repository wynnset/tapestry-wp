# CircleOfSupport

The `CircleOfSupport` acitivity is a TYDE activity that allows users to manage their closest connections and group them into communities.

The data is split into three major pieces &mdash; connections, communities, and circles, with an additional members property representing the connection-communities relationship:

```ts
interface CircleOfSupport {
  id: string
  connections: Connection[]
  communities: Community[]
  circles: Circle[]
}
```

## Connections

A `Connection` represents a single individual. A connection follows the following interface:

```ts
interface Connection {
  id: string
  name: string
  avatar: Emoji
}
```

## Communities

A `Community` represents a group of `Connection`s with some commonality. A `Community` follows the following interface:

```ts
interface Community {
  id: string
  name: string
  icon: Emoji
  color: HexString
  connections: ConnectionId[]
}
```

## Circles

A `Circle` represents connections with equal "closeness" to the user. It is simply an object with a `connections` property:

```ts
interface Circle {
  connections: string[] // Connection ids
}
```

The "closeness" of the circle is determined by the `Circle`'s index in the `CircleOfSupport` `circles` array, where a lower index means the circle represents a closer group of connections.

## Members

A `Member` represents a `Community-Connection` relationship. It is represented as a `members` map that contains `connection id -> community id` key-value pairs.

In the Vue component itself we use this members array to populate the `connections` and `communities` with information about the relationship. For example, we add a `communities` property to each `connection` that contains all of the communities the connection is a part of.
