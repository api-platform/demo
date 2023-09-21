import { type Item } from "@/types/item";
import { type User } from "@/types/User";
import { type Book } from "@/types/Book";

export class Review implements Item {
  public "@id"?: string;

  constructor(
    public body: string,
    public rating: number,
    public book: Book,
    public user: User,
    public publishedAt: Date,
    _id?: string,
  ) {
    this["@id"] = _id;
  }
}
