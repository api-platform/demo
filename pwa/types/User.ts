import { type Item } from "./item";

export class User implements Item {
  public "@id"?: string;

  constructor(
    public firstName: string,
    public lastName: string,
    public name: string,
    public sub: string,
    _id?: string,
  ) {
    this["@id"] = _id;
  }
}
