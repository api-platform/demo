import { FunctionComponent, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { ErrorMessage, Formik } from 'formik';
import { fetch } from 'utils/dataAccess';
import { Review } from 'types/Review';

interface Props {
  review?: Review;
}

export const Form: FunctionComponent<Props> = ({ review }) => {
  const [error, setError] = useState(null);
  const router = useRouter();

  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to delete this item?')) return;

    try {
      await fetch(review['@id'], { method: 'DELETE' });
      router.push('/reviews');
    } catch (error) {
      setError(`Error when deleting the resource: ${error}`);
      console.error(error);
    }
  };

  return (
    <div>
      <h1>{review ? `Edit Review ${review['@id']}` : `Create Review`}</h1>
      <Formik
        initialValues={review ? { ...review } : new Review()}
        validate={(values) => {
          const errors = {};
          // add your validation logic here
          return errors;
        }}
        onSubmit={async (values, { setSubmitting, setStatus, setErrors }) => {
          const isCreation = !values['@id'];
          try {
            await fetch(isCreation ? '/reviews' : values['@id'], {
              method: isCreation ? 'POST' : 'PUT',
              body: JSON.stringify(values),
            });
            setStatus({
              isValid: true,
              msg: `Element ${isCreation ? 'created' : 'updated'}.`,
            });
            router.push('/reviews');
          } catch (error) {
            setStatus({
              isValid: false,
              msg: `${error.defaultErrorMsg}`,
            });
            setErrors(error.fields);
          }
          setSubmitting(false);
        }}>
        {({
          values,
          status,
          errors,
          touched,
          handleChange,
          handleBlur,
          handleSubmit,
          isSubmitting,
        }) => (
          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label className="form-control-label" htmlFor="_body">
                body
              </label>
              <input
                name="body"
                id="_body"
                value={values.body ?? ''}
                type="text"
                placeholder="The actual body of the review"
                className={`form-control${
                  errors.body && touched.body ? ' is-invalid' : ''
                }`}
                aria-invalid={!!(errors.body && touched.body)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage className="text-danger" component="div" name="body" />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_rating">
                rating
              </label>
              <input
                name="rating"
                id="_rating"
                value={values.rating ?? ''}
                type="text"
                placeholder="A rating"
                className={`form-control${
                  errors.rating && touched.rating ? ' is-invalid' : ''
                }`}
                aria-invalid={!!(errors.rating && touched.rating)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage
              className="text-danger"
              component="div"
              name="rating"
            />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_book">
                book
              </label>
              <input
                name="book"
                id="_book"
                value={values.book ?? ''}
                type="text"
                placeholder="The item that is being reviewed/rated"
                className={`form-control${
                  errors.book && touched.book ? ' is-invalid' : ''
                }`}
                aria-invalid={!!(errors.book && touched.book)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage className="text-danger" component="div" name="book" />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_author">
                author
              </label>
              <input
                name="author"
                id="_author"
                value={values.author ?? ''}
                type="text"
                placeholder="The author of the review"
                className={`form-control${
                  errors.author && touched.author ? ' is-invalid' : ''
                }`}
                aria-invalid={!!(errors.author && touched.author)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage
              className="text-danger"
              component="div"
              name="author"
            />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_publicationDate">
                publicationDate
              </label>
              <input
                name="publicationDate"
                id="_publicationDate"
                value={values.publicationDate ?? ''}
                type="text"
                placeholder="Publication date of the review"
                className={`form-control${
                  errors.publicationDate && touched.publicationDate
                    ? ' is-invalid'
                    : ''
                }`}
                aria-invalid={
                  !!(errors.publicationDate && touched.publicationDate)
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage
              className="text-danger"
              component="div"
              name="publicationDate"
            />

            {status && status.msg && (
              <div
                className={`alert ${
                  status.isValid ? 'alert-success' : 'alert-danger'
                }`}
                role="alert">
                {status.msg}
              </div>
            )}

            <button
              type="submit"
              className="btn btn-success"
              disabled={isSubmitting}>
              Submit
            </button>
          </form>
        )}
      </Formik>
      <Link href="/reviews">
        <a className="btn btn-primary">Back to list</a>
      </Link>
      {review && (
        <button className="btn btn-danger" onClick={handleDelete}>
          <a>Delete</a>
        </button>
      )}
    </div>
  );
};
