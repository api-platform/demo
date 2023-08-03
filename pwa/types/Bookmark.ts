import { type Item } from "@/types/item";
import { type Book } from "@/types/Book";

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
