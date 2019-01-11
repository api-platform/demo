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
          htmlFor={`review_${data.input.name}`}
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
          id={`review_${data.input.name}`}
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
          name="body"
          type="text"
          placeholder="The actual body of the review"
        />
        <Field
          component={this.renderField}
          name="rating"
          type="number"
          placeholder="A rating"
          normalize={v => parseFloat(v)}
        />
        <Field
          component={this.renderField}
          name="book"
          type="text"
          placeholder="The item that is being reviewed/rated"
          required={true}
        />
        <Field
          component={this.renderField}
          name="author"
          type="text"
          placeholder="Author the author of the review"
        />
        <Field
          component={this.renderField}
          name="publicationDate"
          type="dateTime"
          placeholder="Author the author of the review"
        />

        <button type="submit" className="btn btn-success">
          Submit
        </button>
      </form>
    );
  }
}

export default reduxForm({
  form: 'review',
  enableReinitialize: true,
  keepDirtyOnReinitialize: true
})(Form);
