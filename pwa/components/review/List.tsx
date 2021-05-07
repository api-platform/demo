import { FunctionComponent } from "react";
import Link from "next/link";
import ReferenceLinks from "components/common/ReferenceLinks";
import { Review } from "types/Review";

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
        <th/>
      </tr>
      </thead>
      <tbody>
      {reviews &&
      reviews.length !== 0 &&
      reviews.map((review) => (
        <tr key={review["@id"]}>
          <th scope="row">
            <ReferenceLinks items={review["@id"]} type="review"/>
          </th>
          <td>{review["body"]}</td>
          <td>{review["rating"]}</td>
          <td><Link href={review["book"]}>{review["book"]}</Link></td>
          <td>{review["author"]}</td>
          <td>{review["publicationDate"]}</td>
          <td>
            <ReferenceLinks
              items={review["@id"]}
              type="review"
              useIcon={true}
            />
          </td>
          <td>
            <Link href={`${review["@id"]}/edit`}>
              <a>
                <i className="bi bi-pen" aria-hidden="true"/>
                <span className="sr-only">Edit</span>
              </a>
            </Link>
          </td>
        </tr>
      ))}
      {/* todo Add pagination */}
      </tbody>
    </table>
  </div>
);
