/**
 * This is a demo component using a demo template.
 * Please remove them and create yours.
 */
import React, {Component, Fragment} from 'react';
import {connect} from 'react-redux';
import {Link} from 'react-router-dom';
import PropTypes from 'prop-types';
import {list, reset} from '../../actions/book/list';
import {itemToLinks} from '../../utils/helpers';

class List extends Component {
  static propTypes = {
    error: PropTypes.string,
    loading: PropTypes.bool.isRequired,
    data: PropTypes.object.isRequired,
    list: PropTypes.func.isRequired,
    reset: PropTypes.func.isRequired,
  };

  componentDidMount() {
    this.props.list(this.props.match.params.page && decodeURIComponent(this.props.match.params.page));
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.match.params.page !== nextProps.match.params.page) nextProps.list(nextProps.match.params.page && decodeURIComponent(nextProps.match.params.page));
  }

  componentWillUnmount() {
    this.props.reset();
  }

  render() {
    return <Fragment>
        <div className="main__aside"></div>
        <div className="main__content">
            <div className="alert alert-warning">This is a demo component using a demo template. Please remove them and create yours.</div>
            <h1>Books List</h1>
            <div className="main__other">
              {this.props.loading && <div className="alert alert-info">Loading...</div>}
              {this.props.error && <div className="alert alert-danger">{this.props.error}</div>}

                <table className="table table-responsive table-striped table-hover">
                <thead>
                  <tr>
                    <th>Id</th>
                    <th>isbn</th>
                    <th>title</th>
                    <th>description</th>
                    <th>author</th>
                    <th>publicationDate</th>
                    <th>reviews</th>
                  </tr>
                </thead>
                <tbody>
                {this.props.data['hydra:member'] && this.props.data['hydra:member'].map(item =>
                  <tr key={item['@id']}>
                    <th scope="row"><Link to={`show/${encodeURIComponent(item['@id'])}`}>{item['@id']}</Link></th>
                    <td>{item['isbn'] ? itemToLinks(item['isbn']) : ''}</td>
                    <td>{item['title'] ? itemToLinks(item['title']) : ''}</td>
                    <td>{item['description'] ? itemToLinks(item['description']) : ''}</td>
                    <td>{item['author'] ? itemToLinks(item['author']) : ''}</td>
                    <td>{item['publicationDate'] ? itemToLinks(item['publicationDate']) : ''}</td>
                    <td>{item['reviews'] ? itemToLinks(item['reviews']) : ''}</td>
                  </tr>
                )}
                </tbody>
              </table>
            </div>
        </div>

      {this.pagination()}
    </Fragment>;
  }

  pagination() {
    const view = this.props.data['hydra:view'];
    if (!view) return;

    const {'hydra:first': first, 'hydra:previous': previous,'hydra:next': next, 'hydra:last': last} = view;

    return <nav aria-label="Page navigation">
        <Link to='.' className={`btn btn-primary${previous ? '' : ' disabled'}`}><span aria-hidden="true">&lArr;</span> First</Link>
        <Link to={!previous || previous === first ? '.' : encodeURIComponent(previous)} className={`btn btn-primary${previous ? '' : ' disabled'}`}><span aria-hidden="true">&larr;</span> Previous</Link>
        <Link to={next ? encodeURIComponent(next) : '#'} className={`btn btn-primary${next ? '' : ' disabled'}`}>Next <span aria-hidden="true">&rarr;</span></Link>
        <Link to={last ? encodeURIComponent(last) : '#'} className={`btn btn-primary${next ? '' : ' disabled'}`}>Last <span aria-hidden="true">&rArr;</span></Link>
    </nav>;
  }
}

const mapStateToProps = (state) => {
  return {
    data: state.book.list.data,
    error: state.book.list.error,
    loading: state.book.list.loading,
  };
};

const mapDispatchToProps = (dispatch) => {
  return {
    list: (page) => dispatch(list(page)),
    reset: () => {
      dispatch(reset());
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(List);
