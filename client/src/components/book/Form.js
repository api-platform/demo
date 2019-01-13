import React, { Component } from 'react';
import { Field, reduxForm } from 'redux-form';
import PropTypes from 'prop-types';

class Form extends Component {
  static propTypes = {
    handleSubmit: PropTypes.func.isRequired,
    error: PropTypes.string
  };

  renderField = data => {
    data.input.className = 'form-control';

    const isInvalid = data.meta.touched && !!data.meta.error;
    if (isInvalid) {
      data.input.className += ' is-invalid';
      data.input['aria-invalid'] = true;
    }

    if (this.props.error && data.meta.touched && !data.meta.error) {
      data.input.className += ' is-valid';
    }

    return (
      <div className={`form-group`}>
        <label
          htmlFor={`book_${data.input.name}`}
          className="form-control-label"
        >
          {data.input.name}
        </label>
        <input
          {...data.input}
          type={data.type}
          step={data.step}
          required={data.required}
          placeholder={data.placeholder}
          id={`book_${data.input.name}`}
        />
        {isInvalid && <div className="invalid-feedback">{data.meta.error}</div>}
      </div>
    );
  };

  render() {
    return (
      <form onSubmit={this.props.handleSubmit}>
        <Field
          component={this.renderField}
          name="isbn"
          type="text"
          placeholder="The ISBN of the book"
        />
        <Field
          component={this.renderField}
          name="title"
          type="text"
          placeholder="The title of the book"
          required={true}
        />
        <Field
          component={this.renderField}
          name="description"
          type="text"
          placeholder="A description of the item"
          required={true}
        />
        <Field
          component={this.renderField}
          name="author"
          type="text"
          placeholder="The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably"
          required={true}
        />
        <Field
          component={this.renderField}
          name="publicationDate"
          type="dateTime"
          placeholder="The date on which the CreativeWork was created or the item was added to a DataFeed"
          required={true}
        />
        <Field
          component={this.renderField}
          name="reviews"
          type="text"
          placeholder="The book's reviews"
          normalize={v => (v === '' ? [] : v.split(','))}
        />

        <button type="submit" className="btn btn-success">
          Submit
        </button>
      </form>
    );
  }
}

export default reduxForm({
  form: 'book',
  enableReinitialize: true,
  keepDirtyOnReinitialize: true
})(Form);
