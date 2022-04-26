export class Review {
  public '@id'?: string;

  constructor(
    _id?: string,
    public id?: string,
    public body?: string,
    public rating?: number,
    public book?: any,
    public author?: string,
    public publicationDate?: string,
  ) {
    this['@id'] = _id;
  }
}
