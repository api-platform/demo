import { type Item } from "./item";
import { type Book } from "./Book";

export class Bookmark implements Item {
  public "@id"?: string;

  constructor(
    public book: Book,
    public bookmarkedAt: Date,
    _id?: string,
  ) {
    this["@id"] = _id;
  }
}
