import { type Item } from "./item";
import { type User } from "./User";
import { type Book } from "./Book";

export class Review implements Item {
  public "@id"?: string;

  constructor(
    public body?: string,
    public rating?: number,
    public book?: Book,
    public user?: User,
    public publishedAt?: Date,
    _id?: string,
  ) {
    this["@id"] = _id;
  }
}
