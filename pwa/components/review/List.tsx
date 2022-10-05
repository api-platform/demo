import { FunctionComponent } from "react";
import Link from "next/link";

import ReferenceLinks from "../common/ReferenceLinks";
import { getPath } from "../../utils/dataAccess";
import { Review } from "../../types/Review";

interface Props {
  reviews: Review[];
}

export const List: FunctionComponent<Props> = ({ reviews }) => (
  <div>
    <h1>Review List</h1>
    <Link href="/reviews/create">
      <a className="btn btn-primary">Create</a>
    </Link>
    <table className="table table-responsive table-striped table-hover">
      <thead>
        <tr>
          <th>id</th>
          <th>body</th>
          <th>rating</th>
          <th>book</th>
          <th>author</th>
          <th>publicationDate</th>
          <th />
        </tr>
      </thead>
      <tbody>
        {reviews &&
          reviews.length !== 0 &&
          reviews.map(
            (review) =>
              review["@id"] && (
                <tr key={review["@id"]}>
                  <th scope="row">
                    <ReferenceLinks
                      items={{
                        href: getPath(review["@id"], "/reviews/[id]"),
                        name: review["@id"],
                      }}
                    />
                  </th>
                  <td>{review["body"]}</td>
                  <td>{review["rating"]}</td>
                  <td>
                    <ReferenceLinks
                      items={{
                        href: getPath(review["book"]["@id"], "/books/[id]"),
                        name: review["book"]["@id"],
                      }}
                    />
                  </td>
                  <td>{review["author"]}</td>
                  <td>{review["publicationDate"]?.toLocaleString()}</td>
                  <td>
                    <Link href={getPath(review["@id"], "/reviews/[id]")}>
                      <a>
                        <i className="bi bi-search" aria-hidden="true"></i>
                        <span className="sr-only">Show</span>
                      </a>
                    </Link>
                  </td>
                  <td>
                    <Link href={getPath(review["@id"], "/reviews/[id]/edit")}>
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
