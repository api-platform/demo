import { FunctionComponent, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/router";
import Head from "next/head";

import ReferenceLinks from "../common/ReferenceLinks";
import { fetch, getPath } from "../../utils/dataAccess";
import { Review } from "../../types/Review";

interface Props {
  review: Review;
  text: string;
}

export const Show: FunctionComponent<Props> = ({ review, text }) => {
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  const handleDelete = async () => {
    if (!review["@id"]) return;
    if (!window.confirm("Are you sure you want to delete this item?")) return;

    try {
      await fetch(review["@id"], { method: "DELETE" });
      router.push("/reviews");
    } catch (error) {
      setError("Error when deleting the resource.");
      console.error(error);
    }
  };

  return (
    <div>
      <Head>
        <title>{`Show Review ${review["@id"]}`}</title>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: text }}
        />
      </Head>
      <h1>{`Show Review ${review["@id"]}`}</h1>
      <table className="table table-responsive table-striped table-hover">
        <thead>
          <tr>
            <th>Field</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row">body</th>
            <td>{review["body"]}</td>
          </tr>
          <tr>
            <th scope="row">rating</th>
            <td>{review["rating"]}</td>
          </tr>
          <tr>
            <th scope="row">book</th>
            <td>
              <ReferenceLinks
                items={{
                  href: getPath(review["book"]["@id"], "/books/[id]"),
                  name: review["book"]["@id"],
                }}
              />
            </td>
          </tr>
          <tr>
            <th scope="row">author</th>
            <td>{review["author"]}</td>
          </tr>
          <tr>
            <th scope="row">publicationDate</th>
            <td>{review["publicationDate"]?.toLocaleString()}</td>
          </tr>
        </tbody>
      </table>
      {error && (
        <div className="alert alert-danger" role="alert">
          {error}
        </div>
      )}
      <Link href="/reviews">
        <a className="btn btn-primary">Back to list</a>
      </Link>{" "}
      <Link href={getPath(review["@id"], "/reviews/[id]/edit")}>
        <a className="btn btn-warning">Edit</a>
      </Link>
      <button className="btn btn-danger" onClick={handleDelete}>
        Delete
      </button>
    </div>
  );
};
