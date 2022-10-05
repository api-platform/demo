import { FunctionComponent, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/router";
import Head from "next/head";

import ReferenceLinks from "../common/ReferenceLinks";
import { fetch, getPath } from "../../utils/dataAccess";
import { Book } from "../../types/Book";

interface Props {
  book: Book;
  text: string;
}

export const Show: FunctionComponent<Props> = ({ book, text }) => {
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  const handleDelete = async () => {
    if (!book["@id"]) return;
    if (!window.confirm("Are you sure you want to delete this item?")) return;

    try {
      await fetch(book["@id"], { method: "DELETE" });
      router.push("/books");
    } catch (error) {
      setError("Error when deleting the resource.");
      console.error(error);
    }
  };

  return (
    <div>
      <Head>
        <title>{`Show Book ${book["@id"]}`}</title>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: text }}
        />
      </Head>
      <h1>{`Show Book ${book["@id"]}`}</h1>
      <table className="table table-responsive table-striped table-hover">
        <thead>
          <tr>
            <th>Field</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row">isbn</th>
            <td>{book["isbn"]}</td>
          </tr>
          <tr>
            <th scope="row">title</th>
            <td>{book["title"]}</td>
          </tr>
          <tr>
            <th scope="row">description</th>
            <td>{book["description"]}</td>
          </tr>
          <tr>
            <th scope="row">author</th>
            <td>{book["author"]}</td>
          </tr>
          <tr>
            <th scope="row">publicationDate</th>
            <td>{book["publicationDate"]?.toLocaleString()}</td>
          </tr>
          <tr>
            <th scope="row">reviews</th>
            <td>
              <ReferenceLinks
                items={book["reviews"].map((emb: any) => ({
                  href: getPath(emb["@id"], "/reviews/[id]"),
                  name: emb["@id"],
                }))}
              />
            </td>
          </tr>
        </tbody>
      </table>
      {error && (
        <div className="alert alert-danger" role="alert">
          {error}
        </div>
      )}
      <Link href="/books">
        <a className="btn btn-primary">Back to list</a>
      </Link>{" "}
      <Link href={getPath(book["@id"], "/books/[id]/edit")}>
        <a className="btn btn-warning">Edit</a>
      </Link>
      <button className="btn btn-danger" onClick={handleDelete}>
        Delete
      </button>
    </div>
  );
};
