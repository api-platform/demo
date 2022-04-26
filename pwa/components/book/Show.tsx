import { FunctionComponent, useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useRouter } from 'next/router';
import { fetch } from 'utils/dataAccess';
import { Book } from 'types/Book';

interface Props {
  book: Book;
}

export const Show: FunctionComponent<Props> = ({ book }) => {
  const [error, setError] = useState(null);
  const router = useRouter();

  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to delete this item?')) return;

    try {
      await fetch(book['@id'], { method: 'DELETE' });
      router.push('/books');
    } catch (error) {
      setError('Error when deleting the resource.');
      console.error(error);
    }
  };

  const handleGenerateCover = async () => {
    try {
      await fetch(`${book['@id']}/generate-cover`, {
        method: 'PUT',
        body: JSON.stringify({}),
      });
    } catch (error) {
      setError('Error when generating the cover.');
      console.error(error);
    }
  };

  return (
    <div>
      <h1>{`Show Book ${book['@id']}`}</h1>
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
            <td>{book['isbn']}</td>
          </tr>
          <tr>
            <th scope="row">title</th>
            <td>{book['title']}</td>
          </tr>
          <tr>
            <th scope="row">description</th>
            <td>{book['description']}</td>
          </tr>
          <tr>
            <th scope="row">author</th>
            <td>{book['author']}</td>
          </tr>
          <tr>
            <th scope="row">publicationDate</th>
            <td>{book['publicationDate']}</td>
          </tr>
          <tr>
            <th scope="row">reviews</th>
            <td>
              {book['reviews'] &&
                book['reviews'].length !== 0 &&
                book['reviews'].map((review: string) => (
                  <div key={review}>
                    <Link href={review}>{review}</Link>
                  </div>
                ))}
            </td>
          </tr>
          <tr>
            <th scope="row">cover</th>
            <td>
              {(book['cover'] && (
                <Image
                  alt="Book cover"
                  src={book['cover']}
                  width={500}
                  height={500}
                />
              )) || (
                <button
                  id="generate-cover"
                  className="btn btn-primary"
                  onClick={handleGenerateCover}>
                  <a>Generate cover</a>
                </button>
              )}
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
      </Link>{' '}
      <Link href={`${book['@id']}/edit`}>
        <a className="btn btn-warning">Edit</a>
      </Link>
      <button className="btn btn-danger" onClick={handleDelete}>
        <a>Delete</a>
      </button>
    </div>
  );
};
