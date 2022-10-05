import { FunctionComponent } from "react";
import Link from "next/link";

import ReferenceLinks from "../common/ReferenceLinks";
import { getPath } from "../../utils/dataAccess";
import { Book } from "../../types/Book";

interface Props {
  books: Book[];
}

export const List: FunctionComponent<Props> = ({ books }) => (
  <div>
    <h1>Book List</h1>
    <Link href="/books/create">
      <a className="btn btn-primary">Create</a>
    </Link>
    <table className="table table-responsive table-striped table-hover">
      <thead>
        <tr>
          <th>id</th>
          <th>isbn</th>
          <th>title</th>
          <th>description</th>
          <th>author</th>
          <th>publicationDate</th>
          <th>reviews</th>
          <th />
        </tr>
      </thead>
      <tbody>
        {books &&
          books.length !== 0 &&
          books.map(
            (book) =>
              book["@id"] && (
                <tr key={book["@id"]}>
                  <th scope="row">
                    <ReferenceLinks
                      items={{
                        href: getPath(book["@id"], "/books/[id]"),
                        name: book["@id"],
                      }}
                    />
                  </th>
                  <td>{book["isbn"]}</td>
                  <td>{book["title"]}</td>
                  <td>{book["description"]}</td>
                  <td>{book["author"]}</td>
                  <td>{book["publicationDate"]?.toLocaleString()}</td>
                  <td>
                    <ReferenceLinks
                      items={book["reviews"].map((emb: any) => ({
                        href: getPath(emb["@id"], "/reviews/[id]"),
                        name: emb["@id"],
                      }))}
                    />
                  </td>
                  <td>
                    <Link href={getPath(book["@id"], "/books/[id]")}>
                      <a>
                        <i className="bi bi-search" aria-hidden="true"></i>
                        <span className="sr-only">Show</span>
                      </a>
                    </Link>
                  </td>
                  <td>
                    <Link href={getPath(book["@id"], "/books/[id]/edit")}>
                      <a>
                        <i className="bi bi-pen" aria-hidden="true" />
                        <span className="sr-only">Edit</span>
                      </a>
                    </Link>
                  </td>
                </tr>
              )
          )}
      </tbody>
    </table>
  </div>
);
